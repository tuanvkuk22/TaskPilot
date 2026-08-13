<?php

namespace ProjectsManager\Modules\Meetings\Handlers;

use Illuminate\Support\Str;
use ProjectsManager\Meeting;
use ProjectsManager\Modules\Meetings\Repositories\MeetingRepository;
use ProjectsManager\Project;
use ProjectsManager\User;

class CreateMeetingHandler
{
    public function __construct(private readonly MeetingRepository $repository) {}

    public function handle(User $user, Project $project, array $data): Meeting
    {
        $meeting = $this->repository->create([
            'project_id' => $project->id,
            'team_id' => $project->team_id,
            'created_by' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'room_name' => 'taskpilot-' . $project->id . '-' . Str::lower(Str::random(24)),
            'status' => 'scheduled',
            'starts_at' => $data['starts_at'] ?? now(),
        ]);

        $meeting->members()->syncWithoutDetaching([$user->id => ['role' => 'host', 'permissions' => json_encode(['meeting.manage', 'meeting.record'])]]);
        $memberIds = $project->users()->pluck('users.id')->merge($project->team?->users()->pluck('users.id') ?? collect())->unique()->values()->all();
        foreach ($memberIds as $memberId) {
            $meeting->members()->syncWithoutDetaching([$memberId => ['role' => 'participant', 'permissions' => json_encode([])]]);
        }

        return $meeting->fresh(['project', 'creator', 'members']);
    }
}
