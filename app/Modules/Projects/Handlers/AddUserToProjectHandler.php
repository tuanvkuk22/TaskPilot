<?php

namespace ProjectsManager\Modules\Projects\Handlers;

use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;
use ProjectsManager\ProjectCategory;

class AddUserToProjectHandler
{
    /**
     * Create a new handler instance.
     *
     * @param ProjectRepository $repository
     */
    public function __construct(
        private readonly ProjectRepository $repository
    ) {
    }

    /**
     * Execute add user to project use case.
     *
     * @param int $currentUserId
     * @param int $projectId
     * @param mixed $emailsInput
     * @param string $role
     * @param array $permissions
     *
     * @return array
     */
    public function handle(int $currentUserId, int $projectId, mixed $emailsInput, string $role = 'member', array $permissions = ['view', 'edit']): array
    {
        $project = $this->repository->findById($projectId);

        if (! $project || $currentUserId !== $project->user_id) {
            return [
                'success' => false,
                'error' => 'Only project owner can add members.',
                'project_id' => $project ? $project->id : 0,
            ];
        }

        $emails = $this->normalizeEmails($emailsInput);

        foreach ($emails as $email) {
            $user = $this->repository->findUserByEmail($email);

            if (! $user) {
                return ['success' => false, 'error' => 'No user was found with that email: ' . $email . '.'];
            }

            if ($this->repository->hasUser($project, $user->id)) {
                return ['success' => false, 'error' => $email . ' already belongs to this project.'];
            }

            $rolePermissions = $project->permissions()
                ->whereJsonContains('roles', $role)
                ->pluck('name')
                ->all();

            $this->repository->attachUser(
                $project,
                $user->id,
                $role,
                array_values(array_unique(array_merge($permissions, $rolePermissions)))
            );

            try {
                if ($user->email) {
                    \Illuminate\Support\Facades\Mail::to($user->email)
                        ->send(new \ProjectsManager\Mail\ProjectInvitationMail($project, \Illuminate\Support\Facades\Auth::user()));
                }
            } catch (\Throwable $e) {
                // Ignore mail sending failure if environment variables are not configured
            }
        }

        return ['success' => true, 'message' => 'Thành viên đã được thêm vào dự án thành công.'];
    }

    /**
     * Normalize emails input.
     *
     * @param mixed $value
     *
     * @return array
     */
    private function normalizeEmails(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        $items = is_array($value) ? $value : preg_split('/[\r\n,;]+/', (string) $value);

        return array_values(array_unique(array_filter(array_map(function ($email) {
            return trim((string) $email);
        }, (array) $items))));
    }
}
