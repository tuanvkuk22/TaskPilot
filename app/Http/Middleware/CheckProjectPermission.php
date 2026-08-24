<?php

namespace ProjectsManager\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckProjectPermission
{
    public function handle($request, Closure $next, ...$permissions)
    {
        $project = $request->route('project');

        if (! $project) {
            abort(404);
        }

        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if ($project->user_id === $user->id) {
            return $next($request);
        }

        $member = $project->users()->where('users.id', $user->id)->first();

        if (! $member) {
            abort(403, 'Bạn không phải thành viên của dự án này.');
        }

        $userPermissions = $member->pivot->permissions ?? '[]';
        if (is_string($userPermissions)) {
            $userPermissions = json_decode($userPermissions, true) ?? [];
        }

        $rolePermissions = [];
        $roleName = $member->pivot->role ?? null;
        if ($roleName) {
            $role = $project->roles()->where('name', $roleName)->first();
            if ($role) {
                $rolePermissions = $role->permissions ?? [];
            }
        }

        $allPermissions = array_values(array_unique(array_merge($userPermissions, $rolePermissions)));

        foreach ($permissions as $permission) {
            if (in_array($permission, $allPermissions, true)) {
                return $next($request);
            }
        }

        abort(403, 'Bạn không có quyền thực hiện hành động này.');
    }
}
