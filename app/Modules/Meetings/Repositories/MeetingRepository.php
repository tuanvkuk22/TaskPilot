<?php

namespace ProjectsManager\Modules\Meetings\Repositories;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Meeting;
use ProjectsManager\MeetingMessage;
use ProjectsManager\Project;
use ProjectsManager\User;

class MeetingRepository
{
    public function accessibleFor(User $user, Project $project): Collection
    {
        return Meeting::with(['creator', 'members'])
            ->where('project_id', $project->id)
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('members', fn ($members) => $members->whereKey($user->id))
                    ->orWhereHas('project.users', fn ($users) => $users->whereKey($user->id))
                    ->orWhereHas('project.team.users', fn ($users) => $users->whereKey($user->id));
            })
            ->latest('starts_at')
            ->get();
    }

    public function find(int $id): ?Meeting
    {
        return Meeting::with(['project', 'team', 'creator', 'members', 'minutes.actions.assignee', 'recordings'])->find($id);
    }

    public function create(array $data): Meeting
    {
        return Meeting::create($data);
    }

    public function messages(Meeting $meeting, User $user, ?int $recipientId = null): Collection
    {
        return $meeting->messages()->with(['user', 'recipient', 'mentions', 'sourceTask', 'attachments'])->where(function ($query) use ($user, $recipientId) {
            if ($recipientId) {
                $query->where(function ($conversation) use ($user, $recipientId) {
                    $conversation->where('user_id', $user->id)->where('recipient_id', $recipientId);
                })->orWhere(function ($conversation) use ($user, $recipientId) {
                    $conversation->where('user_id', $recipientId)->where('recipient_id', $user->id);
                });
            } else {
                $query->whereNull('recipient_id');
            }
        })->oldest()->get();
    }

    public function tasksForMeeting(Meeting $meeting): Collection
    {
        return $meeting->project->tasks()->select(['id', 'name', 'status', 'assignee_id'])->latest()->limit(100)->get();
    }

    public function createMessage(array $data): MeetingMessage
    {
        return MeetingMessage::create($data);
    }
}
