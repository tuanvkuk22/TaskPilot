<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use ProjectsManager\AiAssigneeRecommendation;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\User;
use Tests\TestCase;

class AiRecommendationTest extends TestCase
{
    use RefreshDatabase;

    private function context(): array
    {
        $owner = User::factory()->create();
        $candidate = User::factory()->create(['status' => 'active']);
        $project = Project::create(['name' => 'AI review', 'user_id' => $owner->id]);
        $project->users()->attach($candidate->id);
        $task = Task::withoutEvents(fn () => Task::create([
            'name' => 'Fix login', 'user_id' => $owner->id,
            'project_id' => $project->id, 'status' => 'todo',
        ]));
        return [$owner, $candidate, $project, $task];
    }

    public function test_batch_recommendation_denies_outsiders(): void
    {
        [, , , $task] = $this->context();
        $outsider = User::factory()->create();
        $this->actingAs($outsider)->postJson('/tasks/recommend-assignee-batch', ['task_ids' => [$task->id]])->assertStatus(403);
    }

    public function test_assignment_denies_outsiders_without_mutation(): void
    {
        [, $candidate, , $task] = $this->context();
        $this->actingAs(User::factory()->create())->postJson('/tasks/ai-assign-batch', [
            'assignments' => [['task_id' => $task->id, 'user_id' => $candidate->id]],
        ])->assertStatus(403);
        $this->assertNull($task->fresh()->assignee_id);
    }

    public function test_assignment_rejects_nonmembers(): void
    {
        [$owner, , , $task] = $this->context();
        $outsider = User::factory()->create(['status' => 'active']);
        $this->actingAs($owner)->postJson('/tasks/ai-assign-batch', [
            'assignments' => [['task_id' => $task->id, 'user_id' => $outsider->id]],
        ])->assertStatus(422);
        $this->assertNull($task->fresh()->assignee_id);
    }

    public function test_owner_can_assign_and_stale_retry_cannot_overwrite(): void
    {
        [$owner, $candidate, , $task] = $this->context();
        $payload = ['assignments' => [['task_id' => $task->id, 'user_id' => $candidate->id, 'score' => 0.8]]];
        $this->actingAs($owner)->postJson('/tasks/ai-assign-batch', $payload)->assertOk();
        $this->assertEquals($candidate->id, $task->fresh()->assignee_id);
        $this->assertEquals(1, AiAssigneeRecommendation::where('task_id', $task->id)->count());
        $this->postJson('/tasks/ai-assign-batch', $payload)->assertStatus(409);
        $this->assertEquals(1, AiAssigneeRecommendation::where('task_id', $task->id)->count());
    }

    public function test_member_needs_explicit_assignment_permission(): void
    {
        [, $member, $project, $task] = $this->context();
        $payload = ['assignments' => [['task_id' => $task->id, 'user_id' => $member->id]]];
        $this->actingAs($member)->postJson('/tasks/ai-assign-batch', $payload)->assertStatus(403);
        $project->users()->updateExistingPivot($member->id, ['permissions' => json_encode(['task.assign'])]);
        $this->postJson('/tasks/ai-assign-batch', $payload)->assertOk();
    }

    public function test_mixed_project_batch_is_rejected_before_any_update(): void
    {
        [$owner, $candidate, , $task] = $this->context();
        [, $otherCandidate, , $otherTask] = $this->context();
        $this->actingAs($owner)->postJson('/tasks/ai-assign-batch', ['assignments' => [
            ['task_id' => $task->id, 'user_id' => $candidate->id],
            ['task_id' => $otherTask->id, 'user_id' => $otherCandidate->id],
        ]])->assertStatus(403);
        $this->assertNull($task->fresh()->assignee_id);
        $this->assertNull($otherTask->fresh()->assignee_id);
    }

    public function test_single_recommendation_rejects_project_mismatch(): void
    {
        [$owner, , , $task] = $this->context();
        [, , $otherProject] = $this->context();
        $this->actingAs($owner)->postJson('/tasks/recommend-assignee', [
            'task_id' => $task->id, 'project_id' => $otherProject->id, 'name' => $task->name,
        ])->assertStatus(422);
    }

    public function test_reset_denies_outsiders(): void
    {
        [, , , $task] = $this->context();
        Task::withoutEvents(fn () => $task->update(['labels' => 'ai-test']));
        $this->actingAs(User::factory()->create())->postJson('/tasks/ai-test-reset', [
            'task_ids' => [$task->id],
        ])->assertStatus(403);
    }

    public function test_batch_validation_rejects_duplicate_tasks(): void
    {
        [$owner, $candidate, , $task] = $this->context();
        $row = ['task_id' => $task->id, 'user_id' => $candidate->id];
        $this->actingAs($owner)->postJson('/tasks/ai-assign-batch', ['assignments' => [$row, $row]])->assertStatus(422);
    }
}
