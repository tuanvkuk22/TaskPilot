<?php

namespace ProjectsManager\Modules\Meetings\Services;

use ProjectsManager\Meeting;
use ProjectsManager\Project;
use ProjectsManager\User;

class MeetingAccessService
{
    public const VIEW = 'meeting.view';
    public const MANAGE = 'meeting.manage';
    public const RECORD = 'meeting.record';

    public function can(User $user, Project $project, string $permission): bool
    {
        if ($project->user_id === $user->id || $user->hasRoleName('admin')) return true;
        $projectUser = $project->users()->whereKey($user->id)->first();
        $teamUser = $project->team?->users()->whereKey($user->id)->first();
        foreach ([$projectUser?->pivot, $teamUser?->pivot] as $pivot) {
            $permissions = $this->permissions($pivot?->permissions);
            if ($pivot && in_array($permission, $permissions, true)) return true;
            if ($pivot && in_array('manage_all', $permissions, true)) return true;
            if ($pivot && in_array($pivot->role, ['owner', 'manager', 'lead'], true) && $permission !== self::RECORD) return true;
        }
        return $permission === self::VIEW && (bool) ($projectUser || $teamUser);
    }

    public function canAccess(User $user, Meeting $meeting, string $permission = self::VIEW): bool
    {
        if ($this->can($user, $meeting->project, $permission)) return true;
        $member = $meeting->members()->whereKey($user->id)->first();
        if (!$member) return false;
        if ($permission === self::VIEW) return true;
        $permissions = $this->permissions($member->pivot->permissions);
        return in_array($permission, $permissions, true) || in_array('manage_all', $permissions, true) || ($permission === self::MANAGE && in_array($member->pivot->role, ['host', 'manager'], true));
    }

    private function permissions(mixed $permissions): array
    {
        if (is_string($permissions)) $permissions = json_decode($permissions, true);
        return is_array($permissions) ? $permissions : [];
    }
}