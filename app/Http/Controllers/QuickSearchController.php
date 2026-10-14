<?php

namespace ProjectsManager\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ProjectsManager\Company;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\Team;

class QuickSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function search(Request $request): JsonResponse
    {
        $term = trim($request->query('q', ''));
        $userId = Auth::id();

        if (mb_strlen($term) < 1) {
            return response()->json([
                'query' => '',
                'results' => [
                    'quick_links' => [
                        [
                            'title' => 'Tất cả công việc (My Tasks)',
                            'subtitle' => 'Xem danh sách công việc',
                            'url' => route('tasks.index'),
                            'icon' => 'fa-tasks',
                            'badge' => 'Trang',
                        ],
                        [
                            'title' => 'Tất cả dự án',
                            'subtitle' => 'Xem danh sách dự án',
                            'url' => route('projects.index'),
                            'icon' => 'fa-folder',
                            'badge' => 'Trang',
                        ],
                        [
                            'title' => 'Tạo công việc mới',
                            'subtitle' => 'Thêm nhiệm vụ vào dự án',
                            'url' => route('tasks.create'),
                            'icon' => 'fa-plus-circle',
                            'badge' => 'Hành động',
                        ],
                        [
                            'title' => 'Tạo dự án mới',
                            'subtitle' => 'Khởi tạo workspace dự án',
                            'url' => route('projects.create'),
                            'icon' => 'fa-plus-square',
                            'badge' => 'Hành động',
                        ],
                    ],
                    'tasks' => [],
                    'projects' => [],
                    'teams' => [],
                    'companies' => [],
                ],
            ]);
        }

        // Search Tasks
        $tasks = Task::where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('issue_key', 'like', "%{$term}%");
        })
        ->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->orWhere('assignee_id', $userId)
                  ->orWhereHas('project.users', function ($u) use ($userId) {
                      $u->where('users.id', $userId);
                  });
        })
        ->with('project:id,name')
        ->limit(6)
        ->get()
        ->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => ($task->issue_key ? '[' . $task->issue_key . '] ' : '') . $task->name,
                'subtitle' => $task->project ? 'Dự án: ' . $task->project->name : 'Nhiệm vụ cá nhân',
                'url' => route('tasks.show', $task->id),
                'icon' => 'fa-check-square-o',
                'badge' => $task->status ?? 'open',
            ];
        });

        // Search Projects
        $projects = Project::where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('project_code', 'like', "%{$term}%");
        })
        ->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->orWhereHas('users', function ($u) use ($userId) {
                      $u->where('users.id', $userId);
                  });
        })
        ->limit(5)
        ->get()
        ->map(function ($project) {
            return [
                'id' => $project->id,
                'title' => ($project->project_code ? '[' . $project->project_code . '] ' : '') . $project->name,
                'subtitle' => 'Xem chi tiết / Kanban Board',
                'url' => route('projects.board', $project->id),
                'icon' => 'fa-folder-open-o',
                'badge' => $project->status ?? 'active',
            ];
        });

        // Search Teams
        $teams = Team::where('name', 'like', "%{$term}%")
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhereHas('users', function ($u) use ($userId) {
                          $u->where('users.id', $userId);
                      });
            })
            ->limit(3)
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'title' => $team->name,
                    'subtitle' => 'Nhóm làm việc',
                    'url' => route('teams.show', $team->id),
                    'icon' => 'fa-users',
                    'badge' => 'Team',
                ];
            });

        // Search Companies
        $companies = Company::where('name', 'like', "%{$term}%")
            ->where('user_id', $userId)
            ->limit(3)
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'title' => $company->name,
                    'subtitle' => 'Khách hàng / Doanh nghiệp',
                    'url' => route('companies.show', $company->id),
                    'icon' => 'fa-building-o',
                    'badge' => 'Client',
                ];
            });

        return response()->json([
            'query' => $term,
            'results' => [
                'tasks' => $tasks,
                'projects' => $projects,
                'teams' => $teams,
                'companies' => $companies,
            ],
        ]);
    }
}
