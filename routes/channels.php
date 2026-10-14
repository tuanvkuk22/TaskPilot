<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    return \ProjectsManager\Project::query()->whereKey($projectId)->where(function ($query) use ($user) {
        $query->where('user_id', $user->id)->orWhereHas('users', fn ($members) => $members->where('users.id', $user->id));
    })->exists();
});
