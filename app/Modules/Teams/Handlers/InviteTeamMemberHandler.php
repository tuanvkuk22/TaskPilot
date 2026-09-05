<?php

namespace ProjectsManager\Modules\Teams\Handlers;

use ProjectsManager\Modules\Teams\Repositories\TeamRepository;
use ProjectsManager\Services\NotificationService;
use ProjectsManager\Team;
use Illuminate\Support\Facades\Auth;

class InviteTeamMemberHandler
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
     * Execute invite team members use case.
     *
     * @param Team $team
     * @param mixed $membersInput
     *
     * @return array
     */
    public function handle(Team $team, mixed $membersInput): array
    {
        $emails = $this->normalizeEmails($membersInput);

        if ($emails === []) {
            return ['success' => false, 'error' => 'Hãy nhập ít nhất một email hoặc username để mời.'];
        }

        foreach ($emails as $email) {
            $user = $this->repository->findUserByEmail($email);

            if (! $user) {
                return ['success' => false, 'error' => 'Thành viên ' . $email . ' chưa tồn tại trong hệ thống.'];
            }

            if ($this->repository->hasUser($team, $user->id)) {
                return ['success' => false, 'error' => 'Thành viên ' . $email . ' đã là thành viên của đội nhóm.'];
            }

            $this->repository->attachUser($team, $user->id, 'member', ['view_team']);

            try {
                if ($user->email) {
                    \Illuminate\Support\Facades\Mail::to($user->email)
                        ->send(new \ProjectsManager\Mail\TeamInvitationMail($team, Auth::user()));
                }
            } catch (\Throwable $e) {
                // Ignore mail sending failure if environment variables are not configured
            }

            NotificationService::sendToUser(
                $user,
                NotificationService::EVENT_TEAM_INVITATION,
                'Lời mời tham gia đội nhóm: ' . $team->name,
                Auth::user()->name . ' đã mời bạn vào đội nhóm "' . $team->name . '".',
                Auth::user(),
                ['team_id' => $team->id, 'team_name' => $team->name],
                '/teams/' . $team->id,
            );
        }

        return ['success' => true, 'message' => 'Mời thành viên thành công.'];
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
