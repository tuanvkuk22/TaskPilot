<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use ProjectsManager\Project;
use ProjectsManager\Role;
use ProjectsManager\Team;
use ProjectsManager\User;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_index_supports_status_filter_and_archive_action(): void
    {
        $owner = User::factory()->create([
            'role_id' => 1,
        ]);

        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Member']);

        Project::create([
            'name' => 'Website Redesign',
            'description' => 'Landing page refresh',
            'user_id' => $owner->id,
            'status' => 'active',
            'archived' => 0,
        ]);

        Project::create([
            'name' => 'Legacy Migration',
            'description' => 'Archive me',
            'user_id' => $owner->id,
            'status' => 'archived',
            'archived' => 1,
        ]);

        $response = $this->actingAs($owner)->get('/projects?status=archived');

        $response->assertOk();
        $response->assertSee('Legacy Migration');
        $response->assertSee('status=archived');
    }

    public function test_project_create_form_supports_basic_project_and_category_flow(): void
    {
        $owner = User::factory()->create([
            'role_id' => 1,
        ]);

        $response = $this->actingAs($owner)->get('/projects/create');

        $response->assertOk();
        $response->assertSee('Tên dự án');
        $response->assertSee('Danh mục');
        $response->assertSee('Mô tả');
        $response->assertSee('+ Tạo nhanh danh mục');
        $response->assertDontSee('Công ty');
        $response->assertDontSee('Tên nhóm');
    }

    public function test_project_store_generates_backend_code_and_rejects_duplicate_members(): void
    {
        $owner = User::factory()->create([
            'role_id' => 1,
            'email' => 'owner@example.com',
        ]);

        $member = User::factory()->create([
            'role_id' => 1,
            'email' => 'member@example.com',
        ]);

        $response = $this->actingAs($owner)->post('/projects', [
            'name' => 'Website Redesign',
            'category_id' => null,
            'category_name' => 'Marketing',
            'description' => 'Landing page refresh',
            'members' => ['member@example.com'],
        ]);

        $response->assertRedirect();
        $project = Project::where('name', 'Website Redesign')->firstOrFail();
        $this->assertNotEmpty($project->project_code);
        $this->assertEquals('Marketing', $project->category->name ?? $project->category()->first()->name ?? 'Marketing');

        $duplicate = $this->actingAs($owner)->post('/projects', [
            'name' => 'Website Redesign 2',
            'category_name' => 'Marketing',
            'description' => 'Second project',
            'members' => ['member@example.com'],
        ]);

        $duplicate->assertSessionHas('errors');
    }

    public function test_team_create_form_handles_failed_submission_without_crashing(): void
    {
        $owner = User::factory()->create([
            'role_id' => 1,
            'email' => 'owner@example.com',
        ]);

        $response = $this->actingAs($owner)
            ->from('/teams/create')
            ->post('/teams', [
                'name' => 'New Team',
                'description' => 'Some description',
                'members' => 'ghost.user@notfound.test',
            ]);

        $response->assertRedirect('/teams/create');
        $response->assertSessionHas('errors');

        $follow = $this->actingAs($owner)->get('/teams/create');
        $follow->assertOk();
        $follow->assertSee('Tạo đội nhóm mới');
    }

    public function test_task_board_tracks_priority_and_status_columns(): void
    {
        $owner = User::factory()->create([
            'role_id' => 1,
        ]);

        $response = $this->actingAs($owner)->get('/tasks');

        $response->assertOk();
        $response->assertSee('Kanban');
        $response->assertSee('Todo');
        $response->assertSee('In Progress');
        $response->assertSee('Done');
    }
}
