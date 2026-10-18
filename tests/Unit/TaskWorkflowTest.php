<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\User;

class TaskWorkflowTest extends TestCase
{
    public function test_can_retrieve_active_statuses_and_ignore_inactive(): void
    {
        $project = new Project();
        $project->workflow_config = [
            'statuses' => [
                ['key' => 'todo', 'name' => 'To Do', 'is_active' => true],
                ['key' => 'in_progress', 'name' => 'In Progress', 'is_active' => false],
                ['key' => 'qa', 'name' => 'QA', 'is_active' => true],
                ['key' => 'done', 'name' => 'Done', 'is_active' => true],
            ],
        ];

        $active = $project->getActiveStatuses();
        $this->assertCount(3, $active);
        $this->assertEquals(['todo', 'qa', 'done'], array_column($active, 'key'));
    }

    public function test_can_transition_when_no_rules_configured_except_active(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->user_id = 1;
        $project->workflow_config = [
            'statuses' => [
                ['key' => 'todo', 'name' => 'To Do', 'is_active' => true],
                ['key' => 'in_progress', 'name' => 'In Progress', 'is_active' => true],
                ['key' => 'done', 'name' => 'Done', 'is_active' => true],
            ],
            'transitions' => [],
        ];

        $task = new Task();
        $task->status = 'todo';
        $task->setRelation('project', $project);

        $result = $project->canTransitionTaskStatus($task, 'in_progress', $user);
        $this->assertTrue($result['allowed']);
    }

    public function test_rejects_transition_to_inactive_status(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->user_id = 1;
        $project->workflow_config = [
            'statuses' => [
                ['key' => 'todo', 'name' => 'To Do', 'is_active' => true],
                ['key' => 'deprecated_step', 'name' => 'Deprecated', 'is_active' => false],
            ],
            'transitions' => [
                'todo' => ['deprecated_step'],
            ],
        ];

        $task = new Task();
        $task->status = 'todo';
        $task->setRelation('project', $project);

        $result = $project->canTransitionTaskStatus($task, 'deprecated_step', $user);
        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('vô hiệu hóa', $result['message']);
    }

    public function test_enforces_configured_transition_rules(): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->user_id = 1;
        $project->workflow_config = [
            'statuses' => [
                ['key' => 'todo', 'name' => 'To Do', 'is_active' => true],
                ['key' => 'in_progress', 'name' => 'In Progress', 'is_active' => true],
                ['key' => 'done', 'name' => 'Done', 'is_active' => true],
            ],
            'transitions' => [
                'todo' => ['in_progress'], // Can only go to in_progress from todo
                'in_progress' => ['done', 'todo'],
            ],
        ];

        $task = new Task();
        $task->status = 'todo';
        $task->setRelation('project', $project);

        // Disallowed: todo -> done
        $reject = $project->canTransitionTaskStatus($task, 'done', $user);
        $this->assertFalse($reject['allowed']);
        $this->assertStringContainsString('Quy trình dự án không cho phép', $reject['message']);

        // Allowed: todo -> in_progress
        $allow = $project->canTransitionTaskStatus($task, 'in_progress', $user);
        $this->assertTrue($allow['allowed']);
    }
}
