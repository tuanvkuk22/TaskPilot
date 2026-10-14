<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ProjectsManager\Project;
use ProjectsManager\User;

class DemoNotificationSeeder extends Seeder
{
    /**
     * Add a compact, repeatable set of in-app notifications for the most
     * recently active account. It deliberately does not create users, tasks
     * or projects, and removes only its own previous demo rows.
     */
    public function run(): void
    {
        $user = User::query()
            ->orderByDesc('last_login_at')
            ->orderByDesc('id')
            ->firstOrFail();

        $project = $user->projects()->orderBy('projects.id')->first()
            ?? Project::query()->where('user_id', $user->id)->orderBy('id')->first();

        $projectId = $project?->id;
        $projectUrl = $projectId ? '/projects/' . $projectId : '/projects';
        $now = now();

        DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('data', 'like', '%"demo_key":"taskpilot-notification-demo"%')
            ->delete();

        $notifications = [
            ['task_assigned', 'Bạn được giao một công việc', 'Kiểm tra yêu cầu và cập nhật tiến độ khi bắt đầu.', '/tasks', 3, null],
            ['comment_created', 'Có bình luận mới trong công việc', 'Một thành viên vừa để lại phản hồi cần bạn xem.', '/tasks', 18, null],
            ['sprint_updated', 'Sprint tuần này đã được cập nhật', 'Backlog đã có thay đổi; hãy rà lại các công việc ưu tiên.', $projectUrl . '/sprints', 55, null],
            ['project_updated', 'Tiến độ dự án cần được cập nhật', 'Một số công việc đang chờ xác nhận trạng thái.', $projectUrl, 120, $now->copy()->subMinutes(35)],
            ['worklog_created', 'Đã ghi nhận thời gian làm việc', 'Worklog mới đã được thêm vào báo cáo dự án.', $projectUrl . '/reports', 195, $now->copy()->subHours(2)],
            ['team_invitation', 'Lời mời tham gia đội nhóm', 'Bạn có một lời mời đội nhóm đang chờ phản hồi.', '/teams', 360, null],
        ];

        foreach ($notifications as [$eventType, $title, $message, $url, $minutesAgo, $readAt]) {
            $createdAt = $now->copy()->subMinutes($minutesAgo);

            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'ProjectsManager\\Notifications\\TaskPilotNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'demo_key' => 'taskpilot-notification-demo',
                    'event_type' => $eventType,
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                    'actor_id' => null,
                    'actor_name' => 'TaskPilot',
                    'actor_avatar' => null,
                    'data' => ['project_id' => $projectId],
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => $readAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('Đã thêm 6 thông báo demo cho tài khoản đang hoạt động.');
    }
}
