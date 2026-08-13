<?php

namespace ProjectsManager\Modules\Teams\Handlers;

use ProjectsManager\Modules\Teams\Repositories\TeamRepository;
use ProjectsManager\Team;

class StoreTeamHandler
{
    /**
     * Create a new handler instance.
     *
     * @param TeamRepository $repository
     */
    public function __construct(
        private readonly TeamRepository $repository
    ) {
    }

    /**
     * Execute store team use case.
     *
     * @param int $userId
     * @param array $data
     *
     * @return array
     */
    public function handle(int $userId, array $data): array
    {
        $team = $this->repository->create([
            'name' => trim((string) $data['name']),
            'description' => $data['description'] ?? null,
            'user_id' => $userId,
        ]);

        $this->repository->attachUser($team, $userId, 'owner', ['manage_team', 'invite_members', 'view_team']);

        $emails = $this->normalizeEmails($data['members'] ?? null);
        foreach ($emails as $email) {
            $user = $this->repository->findUserByEmail($email);

            if (! $user) {
                return ['success' => false, 'error' => 'Thành viên ' . $email . ' chưa tồn tại trong hệ thống.'];
            }

            if ($this->repository->hasUser($team, $user->id)) {
                return ['success' => false, 'error' => 'Thành viên ' . $email . ' đã có trong đội nhóm.'];
            }

            $this->repository->attachUser($team, $user->id, 'member', ['view_team']);

            try {
                if ($user->email) {
                    \Illuminate\Support\Facades\Mail::to($user->email)
                        ->send(new \ProjectsManager\Mail\TeamInvitationMail($team, \Illuminate\Support\Facades\Auth::user()));
                }
            } catch (\Throwable $e) {
                // Ignore mail sending failure if environment variables are not configured
            }
        }

        return ['success' => true, 'team' => $team];
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

        $values = is_array($value) ? $value : preg_split('/[\r\n,;]+/', (string) $value);

        return array_values(array_unique(array_filter(array_map(function ($email) {
            return trim((string) $email);
        }, (array) $values))));
    }
}
