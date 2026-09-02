<?php

use Illuminate\Database\Seeder;
use ProjectsManager\Role;
use ProjectsManager\User;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\Worklog;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Roles
        Role::firstOrCreate(['id' => 1], ['name' => 'Admin']);
        Role::firstOrCreate(['id' => 2], ['name' => 'Manager']);
        Role::firstOrCreate(['id' => 3], ['name' => 'Member']);

        // 2. Primary User
        $user1 = User::firstOrCreate(
            ['email' => 'trannguyentuan1807@gmail.com'],
            [
                'name' => 'Tuấn Trần Nguyễn',
                'password' => bcrypt('12345678'),
                'role_id' => 1,
                'status' => 'active',
            ]
        );
        $user1->password = bcrypt('12345678');
        $user1->save();

        $user2 = User::firstOrCreate(
            ['email' => 'nguyenvana@example.com'],
            [
                'name' => 'Nguyễn Văn A',
                'password' => bcrypt('12345678'),
                'role_id' => 3,
                'status' => 'active',
            ]
        );

        $user3 = User::firstOrCreate(
            ['email' => 'buithic@example.com'],
            [
                'name' => 'Bùi Thị C',
                'password' => bcrypt('12345678'),
                'role_id' => 3,
                'status' => 'active',
            ]
        );

        // 3. Projects
        $proj1 = Project::firstOrCreate(
            ['id' => 5],
            [
                'name' => 'Education Project (Lớp học trực tuyến)',
                'project_code' => 'EDU',
                'description' => 'Hệ thống quản lý lớp học trực tuyến, ngân hàng đề thi và tiến độ học tập.',
                'user_id' => $user1->id,
                'status' => 'active',
                'start_date' => '2026-06-01',
                'end_date' => '2026-12-31',
                'budget' => 150000000,
                'priority' => 'high',
            ]
        );

        $proj2 = Project::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'TaskPilot Core Redesign',
                'project_code' => 'TP',
                'description' => 'Nâng cấp toàn bộ hệ thống TaskPilot theo chuẩn Jira Agile.',
                'user_id' => $user1->id,
                'status' => 'active',
                'start_date' => '2026-07-01',
                'end_date' => '2026-11-30',
                'budget' => 200000000,
                'priority' => 'high',
            ]
        );

        // 4. Tasks for Education Project (Project ID 5)
        // Clear old tasks if re-seeding
        Task::whereIn('project_id', [1, 5])->delete();

        // Epic 1
        $epic1 = Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'name' => 'Lớp học trực tuyến',
            'issue_type' => 'epic',
            'status' => 'in_progress',
            'priority' => 'high',
            'start_date' => '2026-06-15',
            'due_date' => '2026-09-30',
            'story_points' => 20,
            'description' => 'Phát triển mô-đun lớp học WebRTC trực tuyến.',
        ]);

        $story1 = Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'parent_id' => $epic1->id,
            'name' => '[LHTT] [01] Complete online class integration',
            'issue_type' => 'story',
            'status' => 'in_progress',
            'priority' => 'high',
            'assignee_id' => $user1->id,
            'reporter_id' => $user1->id,
            'start_date' => '2026-07-01',
            'due_date' => '2026-08-30',
            'story_points' => 8,
            'labels' => 'frontend, webrtc',
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'parent_id' => $story1->id,
            'name' => 'Cấu hình WebRTC Video Stream Server',
            'issue_type' => 'subtask',
            'status' => 'done',
            'priority' => 'medium',
            'assignee_id' => $user1->id,
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'parent_id' => $story1->id,
            'name' => 'Kiểm thử kết nối WebRTC trên thiết bị di động',
            'issue_type' => 'subtask',
            'status' => 'in_progress',
            'priority' => 'low',
            'assignee_id' => $user1->id,
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'parent_id' => $epic1->id,
            'name' => '[LHTT] [04] Fix missing center class avatar',
            'issue_type' => 'bug',
            'status' => 'done',
            'priority' => 'high',
            'assignee_id' => $user1->id,
            'reporter_id' => $user2->id,
            'start_date' => '2026-07-10',
            'due_date' => '2026-08-15',
            'story_points' => 3,
            'labels' => 'bug, ui',
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'parent_id' => $epic1->id,
            'name' => '[LHTT] [02] Improve Teacher Online Dashboard',
            'issue_type' => 'task',
            'status' => 'done',
            'priority' => 'medium',
            'assignee_id' => $user2->id,
            'reporter_id' => $user1->id,
            'start_date' => '2026-07-05',
            'due_date' => '2026-08-20',
            'story_points' => 5,
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'parent_id' => $epic1->id,
            'name' => '[LHTT] [03] Add Teacher Monthly Analytics Report',
            'issue_type' => 'task',
            'status' => 'in_progress',
            'priority' => 'medium',
            'assignee_id' => $user2->id,
            'reporter_id' => $user1->id,
            'start_date' => '2026-07-20',
            'due_date' => '2026-09-10',
            'story_points' => 5,
            'labels' => 'backend, report',
        ]);

        // Epic 2
        $epic2 = Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'name' => 'Quản lý Đề thi & Chấm điểm',
            'issue_type' => 'epic',
            'status' => 'in_progress',
            'priority' => 'high',
            'start_date' => '2026-07-01',
            'due_date' => '2026-10-31',
            'story_points' => 15,
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'parent_id' => $epic2->id,
            'name' => 'Tự động chấm điểm trắc nghiệm trực tuyến',
            'issue_type' => 'task',
            'status' => 'in_progress',
            'priority' => 'high',
            'assignee_id' => $user3->id,
            'start_date' => '2026-07-15',
            'due_date' => '2026-09-15',
            'story_points' => 8,
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'parent_id' => $epic2->id,
            'name' => 'Xuất báo cáo kết quả thi ra file Excel và PDF',
            'issue_type' => 'task',
            'status' => 'todo',
            'priority' => 'medium',
            'assignee_id' => $user3->id,
            'start_date' => '2026-08-01',
            'due_date' => '2026-09-30',
            'story_points' => 5,
        ]);

        // Standalone Tasks
        Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'name' => 'Quy trình Xin nghỉ dạy & Duyệt đơn',
            'issue_type' => 'task',
            'status' => 'todo',
            'priority' => 'low',
            'assignee_id' => $user2->id,
            'start_date' => '2026-08-10',
            'due_date' => '2026-09-20',
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'user_id' => $user1->id,
            'name' => 'Lịch sử giảng dạy & Điểm danh học sinh',
            'issue_type' => 'task',
            'status' => 'todo',
            'priority' => 'medium',
            'assignee_id' => $user3->id,
            'start_date' => '2026-08-15',
            'due_date' => '2026-09-25',
        ]);

        // 5. Worklogs for testing the time tracking screens
        $worklogTasks = Task::whereIn('project_id', [$proj1->id, $proj2->id])
            ->where('issue_type', '!=', 'epic')
            ->orderBy('id')
            ->take(6)
            ->get();

        $worklogData = [
            ['hours' => 2.50, 'logged_at' => '2026-08-18', 'description' => 'Phân tích yêu cầu và chuẩn bị công việc.', 'user_id' => $user1->id],
            ['hours' => 4.00, 'logged_at' => '2026-08-19', 'description' => 'Phát triển chức năng chính.', 'user_id' => $user2->id],
            ['hours' => 1.75, 'logged_at' => '2026-08-20', 'description' => 'Kiểm thử luồng xử lý.', 'user_id' => $user3->id],
            ['hours' => 3.25, 'logged_at' => '2026-08-21', 'description' => 'Sửa lỗi và hoàn thiện giao diện.', 'user_id' => $user1->id],
            ['hours' => 5.00, 'logged_at' => '2026-08-22', 'description' => 'Tích hợp và kiểm tra trên môi trường phát triển.', 'user_id' => $user2->id],
            ['hours' => 2.00, 'logged_at' => '2026-08-25', 'description' => 'Rà soát kết quả và cập nhật tài liệu.', 'user_id' => $user3->id],
        ];

        foreach ($worklogTasks as $index => $task) {
            if (!isset($worklogData[$index])) {
                break;
            }

            Worklog::create($worklogData[$index] + ['task_id' => $task->id]);
        }
    }
}
