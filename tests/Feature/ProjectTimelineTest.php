<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use ProjectsManager\Project;
use ProjectsManager\Role;
use ProjectsManager\Task;
use ProjectsManager\User;
use Tests\TestCase;

class ProjectTimelineTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Member']);
    }

    public function test_timeline_page_renders_successfully_with_project_and_tasks(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $project = Project::create([
            'name' => 'Education Project',
            'project_code' => 'EDU',
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $epic = Task::create([
            'name' => 'Lớp học trực tuyến',
            'project_id' => $project->id,
            'user_id' => $user->id,
            'issue_type' => 'epic',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $task = Task::create([
            'name' => '[LHTT] Complete online class',
            'project_id' => $project->id,
            'user_id' => $user->id,
            'parent_id' => $epic->id,
            'issue_type' => 'story',
            'status' => 'in_progress',
            'priority' => 'medium',
            'assignee_id' => $user->id,
        ]);

        $subtask = Task::create([
            'name' => 'Fix video stream issue',
            'project_id' => $project->id,
            'user_id' => $user->id,
            'parent_id' => $task->id,
            'issue_type' => 'subtask',
            'status' => 'todo',
            'priority' => 'low',
        ]);

        $response = $this->actingAs($user)->get("/projects/{$project->id}/timeline");

        $response->assertStatus(200);
        $response->assertSee('projectTimelineData');
        $response->assertSee('Education Project');
        $response->assertSee('project_code');
    }

    public function test_timeline_tasks_contain_proper_hierarchy_relations(): void
    {
        $user = User::create([
            'name' => 'Developer User',
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $project = Project::create([
            'name' => 'TaskPilot Dev',
            'project_code' => 'TP',
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $epic = Task::create([
            'name' => 'Epic Feature Alpha',
            'project_id' => $project->id,
            'user_id' => $user->id,
            'issue_type' => 'epic',
            'status' => 'todo',
        ]);

        $childTask = Task::create([
            'name' => 'Child Task Beta',
            'project_id' => $project->id,
            'user_id' => $user->id,
            'parent_id' => $epic->id,
            'issue_type' => 'task',
            'status' => 'in_progress',
        ]);

        $subtask = Task::create([
            'name' => 'Subtask Gamma',
            'project_id' => $project->id,
            'user_id' => $user->id,
            'parent_id' => $childTask->id,
            'issue_type' => 'subtask',
            'status' => 'todo',
        ]);

        $this->assertEquals($epic->id, $childTask->fresh()->parent_id);
        $this->assertEquals($childTask->id, $subtask->fresh()->parent_id);
        $this->assertCount(1, $epic->fresh()->children);
        $this->assertCount(1, $childTask->fresh()->children);
    }

    public function test_task_move_status_api_updates_task_status(): void
    {
        $user = User::create([
            'name' => 'QA Lead',
            'email' => 'qa@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $project = Project::create([
            'name' => 'Testing Project',
            'project_code' => 'TEST',
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $task = Task::create([
            'name' => 'Test Task Move',
            'project_id' => $project->id,
            'user_id' => $user->id,
            'status' => 'todo',
            'issue_type' => 'task',
        ]);

        $response = $this->actingAs($user)->postJson("/tasks/{$task->id}/move", [
            'status' => 'done',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('done', $task->fresh()->status);
    }
}
