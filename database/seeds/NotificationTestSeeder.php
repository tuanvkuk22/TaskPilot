<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use ProjectsManager\Project;
use ProjectsManager\Role;
use ProjectsManager\Sprint;
use ProjectsManager\Task;
use ProjectsManager\User;
use ProjectsManager\Worklog;

class NotificationTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $oldProjectIds = Project::where('name', 'LIKE', 'Test Noti%')->pluck('id')->toArray();
        foreach ($oldProjectIds as $pid) {
            Task::where('project_id', $pid)->delete();
            Worklog::whereIn('task_id', Task::where('project_id', $pid)->pluck('id'))->delete();
            Sprint::where('project_id', $pid)->delete();
            DB::table('notifications')->where('data', 'like', '%"project_id":' . $pid)->delete();
        }
        Project::where('name', 'LIKE', 'Test Noti%')->delete();
        User::where('email', 'LIKE', '%test.noti%')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Role::firstOrCreate(['id' => 1], ['name' => 'Admin']);
        Role::firstOrCreate(['id' => 2], ['name' => 'Manager']);
        Role::firstOrCreate(['id' => 3], ['name' => 'Member']);

        $users = [];
        $password = 'password123';

        $owner = User::firstOrCreate(
            ['email' => 'trannguyentuan1807@gmail.com'],
            [
                'name' => 'Tuấn TP',
                'first_name' => 'Tuấn',
                'last_name' => 'TP',
                'password' => Hash::make($password),
                'role_id' => 1,
                'status' => 'active',
                'email_notifications' => true,
                'in_app_notifications' => true,
            ]
        );
        $owner->password = Hash::make($password);
        $owner->email_notifications = true;
        $owner->in_app_notifications = true;
        $owner->save();
        $users[] = $owner;

        for ($i = 1; $i <= 9; $i++) {
            $email = 'test.noti' . $i . '@example.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Test User ' . $i,
                    'password' => Hash::make($password),
                    'role_id' => 3,
                    'status' => 'active',
                    'email_notifications' => ($i % 2 === 0),
                    'in_app_notifications' => true,
                ]
            );
            $user->password = Hash::make($password);
            $user->email_notifications = ($i % 2 === 0);
            $user->in_app_notifications = true;
            $user->save();
            $users[] = $user;
        }

        $this->command->info('10 users created with password: ' . $password);

        for ($p = 1; $p <= 10; $p++) {
            $projectOwner = $users[$p % 10];

            $project = Project::create([
                'name' => 'Test Noti Project #' . $p,
                'project_code' => 'TNP' . $p . '-' . now()->format('Ymd'),
                'description' => 'Dự án test thông báo #' . $p . ' với 10 công việc.',
                'user_id' => $projectOwner->id,
                'status' => 'active',
                'priority' => ['high', 'medium', 'low'][($p - 1) % 3],
                'start_date' => '2026-09-01',
                'end_date' => '2026-12-31',
                'archived' => false,
            ]);

            for ($u = 0; $u < count($users); $u++) {
                if ($users[$u]->id === $projectOwner->id) {
                    $role = 'owner';
                    $permissions = [
                        'project.manage', 'project.edit', 'project.view',
                        'task.manage', 'task.view', 'task.create', 'task.edit',
                        'member.manage', 'member.view', 'member.invite',
                        'comment.create', 'comment.view',
                    ];
                } else {
                    $role = ($u % 2 === 0) ? 'member' : 'viewer';
                    $permissions = $role === 'member'
                        ? ['project.view', 'project.edit', 'task.view', 'task.create', 'task.edit', 'comment.create', 'comment.view']
                        : ['project.view', 'task.view', 'member.view', 'comment.view'];
                }

                $project->users()->attach($users[$u]->id, [
                    'role' => $role,
                    'permissions' => json_encode($permissions),
                ]);
            }

            for ($t = 1; $t <= 10; $t++) {
                $taskOwner = $users[($p + $t) % 10];
                $assignee = $users[($p + $t + 3) % 10];

                $task = Task::create([
                    'project_id' => $project->id,
                    'user_id' => $taskOwner->id,
                    'name' => 'Task #' . $t . ' - ' . $project->name,
                    'issue_type' => ['task', 'story', 'bug'][($t - 1) % 3],
                    'status' => ['todo', 'in_progress', 'done'][($t - 1) % 3],
                    'priority' => ['high', 'medium', 'low'][($t - 1) % 3],
                    'assignee_id' => $assignee->id,
                    'reporter_id' => $taskOwner->id,
                    'story_points' => $t * 2,
                ]);
                $task->update(['issue_key' => 'TNP' . $p . '-' . str_pad((string) $task->id, 4, '0', STR_PAD_LEFT)]);
            }
        }

        foreach (Project::where('name', 'LIKE', 'Test Noti%')->get() as $p) {
            $tasks = $p->tasks()->limit(3)->get();
            foreach ($tasks as $task) {
                $commenter = $users[($task->id + 1) % 10];
                if ($commenter->id !== $task->user_id) {
                    \ProjectsManager\Comment::create([
                        'commentable_type' => Task::class,
                        'commentable_id' => $task->id,
                        'user_id' => $commenter->id,
                        'body' => 'Bình luận test thông báo cho công việc ' . $task->issue_key . '.',
                    ]);
                }
            }
        }

        foreach (Project::where('name', 'LIKE', 'Test Noti%')->get() as $p) {
            $tasks = $p->tasks()->whereNotNull('assignee_id')->where('user_id', '!=', \ProjectsManager\User::find($p->user_id)->id)->limit(2)->get();
            foreach ($tasks as $task) {
                Worklog::create([
                    'task_id' => $task->id,
                    'user_id' => $task->user_id,
                    'assignee_id' => $task->assignee_id,
                    'hours' => 2.5,
                    'logged_at' => '2026-09-01',
                    'description' => 'Worklog test thông báo cho task ' . $task->issue_key . '.',
                ]);
            }
        }

        $totalUsers = count($users);
        $totalNotifications = DB::table('notifications')->count();
        $ownerNotifications = $users[0]->notifications()->count();
        $memberNotifications = $users[1]->notifications()->count();

        $this->command->info('');
        $this->command->info('=== Kết quả test ===');
        $this->command->info('Users: ' . $totalUsers);
        $this->command->info('Projects: ' . Project::where('name', 'LIKE', 'Test Noti%')->count());
        $this->command->info('Tasks: ' . Task::whereHas('project', function($q) { $q->where('name', 'LIKE', 'Test Noti%'); })->count());
        $this->command->info('Total notifications: ' . $totalNotifications);
        $this->command->info('');
        $this->command->info('Owner (trannguyentuan1807@gmail.com): ' . $ownerNotifications . ' notifications');
        $this->command->info('Member1 (test.noti1@example.com): ' . $memberNotifications . ' notifications');
        $this->command->info('');
        $this->command->info('All passwords: ' . $password);
        $this->command->info('');
        $this->command->info('Login: trannguyentuan1807@gmail.com / ' . $password);
        $this->command->info('Or: test.noti1@example.com / ' . $password);
        $this->command->info('Or: test.notiN@example.com (N=1..9) / ' . $password);
        $this->command->info('Notification Center: /notifications');
    }
}
