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
        $response->assertViewHas('projects', fn ($projects) =>
            $projects->count() === 1 && $projects->first()->status === 'archived'
        );
    }

    public function test_project_create_form_supports_basic_project_and_category_flow(): void
    {
        $owner = User::factory()->create([
            'role_id' => 1,
        ]);

        $response = $this->actingAs($owner)->get('/projects/create');

        $response->assertOk();
        $response->assertSee('id="project-create-app"', false);
        $response->assertViewHasAll(['companies', 'categories', 'teams']);
        $response->assertSee(json_encode(route('projects.store')), false);
        $response->assertSee(json_encode(route('projects.categories.store')), false);
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
            'members' => 'member@example.com',
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $project = Project::where('name', 'Website Redesign')->firstOrFail();
        $this->assertNotEmpty($project->project_code);
        $this->assertSame('Marketing', $project->category->name);
        $this->assertTrue($project->users()->where('users.id', $member->id)->exists());

        $team = Team::create(['name' => 'Marketing team', 'user_id' => $owner->id]);
        $team->users()->attach($member->id);

        $duplicate = $this->actingAs($owner)->post('/projects', [
            'name' => 'Website Redesign 2',
            'team_id' => $team->id,
            'category_name' => 'Marketing',
            'description' => 'Second project',
            'members' => 'member@example.com',
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $duplicate->assertSessionHasErrors('project');
        $this->assertDatabaseMissing('projects', ['name' => 'Website Redesign 2']);
    }

    public function test_project_store_rejects_every_missing_member_before_creating_the_project(): void
    {
        $owner = User::factory()->create([
            'role_id' => 1,
            'email' => 'owner@example.com',
        ]);

        $response = $this->actingAs($owner)
            ->from('/projects/create')
            ->post('/projects', [
                'name' => 'Project With Missing Members',
                'members' => 'first.missing@example.com, second.missing@example.com',
                'status' => 'active',
                'priority' => 'medium',
            ]);

        $response->assertRedirect('/projects/create');
        $response->assertSessionHasErrors('project');
        $this->assertStringContainsString(
            'first.missing@example.com, second.missing@example.com',
            session('errors')->first('project')
        );
        $this->assertDatabaseMissing('projects', ['name' => 'Project With Missing Members']);
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
        $response->assertSee('id="task-index-app"', false);
        $response->assertViewHas('stats', fn ($stats) =>
            isset($stats['pending'], $stats['in_progress'], $stats['done'], $stats['high_priority'])
        );
        $response->assertViewHasAll(['selectedStatus', 'selectedPriority']);
    }
}
