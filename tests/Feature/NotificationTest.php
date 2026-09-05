<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProjectsManager\Notification as NotificationModel;
use ProjectsManager\Project;
use ProjectsManager\Role;
use ProjectsManager\Task;
use ProjectsManager\User;
use ProjectsManager\Worklog;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Member']);
    }

    public function test_task_created_sends_notification_to_project_members(): void
    {
        Notification::fake();

        $owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $project = Project::create([
            'name' => 'Test Project',
            'user_id' => $owner->id,
            'status' => 'active',
        ]);

        $project->users()->attach($owner->id, ['role' => 'owner', 'permissions' => json_encode([])]);
        $project->users()->attach($member->id, ['role' => 'member', 'permissions' => json_encode([])]);

        $task = Task::create([
            'name' => 'Test Task',
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'issue_type' => 'task',
            'issue_key' => 'TP-00001',
        ]);

        Notification::assertSentTo(
            [$member],
            \ProjectsManager\Notifications\TaskPilotNotification::class
        );

        Notification::assertNotSentTo(
            $owner,
            \ProjectsManager\Notifications\TaskPilotNotification::class
        );
    }

    public function test_notification_preferences_respected(): void
    {
        Notification::fake();

        $owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner2@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
            'email_notifications' => false,
        ]);

        $member = User::create([
            'name' => 'Member User',
            'email' => 'member2@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
            'email_notifications' => true,
        ]);

        $project = Project::create([
            'name' => 'Test Project 2',
            'user_id' => $owner->id,
            'status' => 'active',
        ]);

        $project->users()->attach($owner->id, ['role' => 'owner', 'permissions' => json_encode([])]);
        $project->users()->attach($member->id, ['role' => 'member', 'permissions' => json_encode([])]);

        $notification = new \ProjectsManager\Notifications\TaskPilotNotification(
            'test.event',
            'Test Notification',
            'This is a test message',
            ['test' => true],
            null,
            $owner
        );

        $this->assertEquals(['database'], $notification->via($owner));
        $this->assertContains('mail', $notification->via($member));
    }

    public function test_user_can_view_notifications_index(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'viewtest@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $project = Project::create([
            'name' => 'View Test Project',
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $project->users()->attach($user->id, ['role' => 'owner', 'permissions' => json_encode([])]);
        $project->users()->attach(User::create(['name' => 'Other User', 'email' => 'other@example.com', 'password' => bcrypt('pass'), 'role_id' => 1])->id, ['role' => 'member', 'permissions' => json_encode([])]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');
    }

    public function test_user_can_get_unread_count(): void
    {
        $user = User::create([
            'name' => 'Count Test User',
            'email' => 'counttest@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $user->notify(new \ProjectsManager\Notifications\TaskPilotNotification(
            'test.event',
            'Test',
            'Test message',
            ['test' => true],
            null,
            null
        ));

        $response = $this->actingAs($user)->get(route('notifications.unread-count'));

        $response->assertOk();
        $response->assertJson(['count' => 1]);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::create([
            'name' => 'Read Test User',
            'email' => 'readtest@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $user->notify(new \ProjectsManager\Notifications\TaskPilotNotification(
            'test.event',
            'Test',
            'Test message',
            ['test' => true],
            null,
            null
        ));

        $notification = $user->notifications()->first();

        $this->assertNull($notification->read_at);

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_delete_notification(): void
    {
        $user = User::create([
            'name' => 'Delete Test User',
            'email' => 'deletetest@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $user->notify(new \ProjectsManager\Notifications\TaskPilotNotification(
            'test.event',
            'Test',
            'Test message',
            ['test' => true],
            null,
            null
        ));

        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)->deleteJson(route('notifications.destroy', $notification->id));

        $response->assertOk();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }
}
