<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ProjectsManager\Policies\TaskPolicy;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\User;

class TaskPolicyTest extends TestCase
{
    public function test_project_owner_can_assign_without_membership(): void
    {
        $user = new User();
        $user->id = 7;
        $task = new Task(['project_id' => 3]);
        $task->setRelation('project', new Project(['user_id' => '7']));
        $this->assertTrue((new TaskPolicy())->assign($user, $task));
    }

    public function test_personal_task_is_restricted_to_its_owner(): void
    {
        $user = new User();
        $user->id = 7;
        $policy = new TaskPolicy();
        $this->assertTrue($policy->assign($user, new Task(['user_id' => 7])));
        $this->assertFalse($policy->assign($user, new Task(['user_id' => 8])));
    }

    public function test_missing_project_is_denied(): void
    {
        $user = new User();
        $user->id = 7;
        $task = new Task(['project_id' => 3]);
        $task->setRelation('project', null);
        $this->assertFalse((new TaskPolicy())->assign($user, $task));
    }
}
