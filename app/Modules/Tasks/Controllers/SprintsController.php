<?php

namespace ProjectsManager\Modules\Tasks\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use ProjectsManager\Events\ProjectTaskUpdated;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Project;
use ProjectsManager\Sprint;
use ProjectsManager\Task;

class SprintsController extends Controller
{
    public function index(Project $project): View
    {
        // Tự động đóng sprint đang chạy nếu đã quá hạn ngày kết thúc
        Sprint::closeExpiredSprints($project->id);

        $project->load([
            'sprints' => function ($query) {
                $query->with(['tasks' => function ($tasks) {
                    $tasks->with('assignee')->orderByDesc('priority')->orderBy('id');
                }])
                ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'planned' THEN 1 ELSE 2 END")
                ->orderBy('starts_at');
            },
            'users',
        ]);

        $backlogTasks = $project->tasks()
            ->whereNull('sprint_id')
            ->with('assignee')
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();

        $movableSprints = $project->sprints()
            ->whereIn('status', ['planned', 'active'])
            ->orderBy('name')
            ->get();

        $doneStatuses = ['done', 'closed', 'completed'];

        // Agile KPI calculations
        $totalBacklogPoints = $backlogTasks->sum('story_points');
        $activeSprint = $project->sprints->firstWhere('status', 'active');
        $activeSprintPoints = $activeSprint ? $activeSprint->tasks->sum('story_points') : 0;
        $activeSprintDonePoints = $activeSprint
            ? $activeSprint->tasks->filter(fn ($t) => in_array(strtolower($t->status ?? ''), $doneStatuses))->sum('story_points')
            : 0;

        return view('sprints.index', [
            'project' => $project,
            'backlog' => $backlogTasks,
            'movableSprints' => $movableSprints,
            'doneStatuses' => $doneStatuses,
            'totalBacklogPoints' => $totalBacklogPoints,
            'activeSprint' => $activeSprint,
            'activeSprintPoints' => $activeSprintPoints,
            'activeSprintDonePoints' => $activeSprintDonePoints,
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'goal' => ['nullable', 'string', 'max:1000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $project->sprints()->create(array_merge($data, [
            'status' => 'planned',
        ]));

        return back()->with('success', 'Sprint mới đã được tạo thành công.');
    }

    public function start(Request $request, $param1, $param2 = null): RedirectResponse
    {
        $sprint = $param2 instanceof Sprint ? $param2 : ($param1 instanceof Sprint ? $param1 : Sprint::findOrFail($param2 ?: $param1));

        // Deactivate other active sprints for this project
        $sprint->project->sprints()->where('status', 'active')->update(['status' => 'closed']);

        $sprint->update([
            'status' => 'active',
            'starts_at' => $sprint->starts_at ?: now()->toDateString(),
        ]);

        return back()->with('success', "Sprint \"{$sprint->name}\" đã được bắt đầu.");
    }

    public function close(Request $request, $param1, $param2 = null): RedirectResponse
    {
        $sprint = $param2 instanceof Sprint ? $param2 : ($param1 instanceof Sprint ? $param1 : Sprint::findOrFail($param2 ?: $param1));

        $sprint->update(['status' => 'closed']);

        return back()->with('success', "Sprint \"{$sprint->name}\" đã hoàn thành và được đóng lại.");
    }

    /** Move a task to a sprint or return it to the backlog */
    public function moveTask(Request $request, Project $project, Task $task): JsonResponse|RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        $data = $request->validate([
            'sprint_id' => ['nullable', 'integer', 'exists:sprints,id'],
        ]);

        $sprint = empty($data['sprint_id']) ? null : $project->sprints()->findOrFail($data['sprint_id']);

        $task->update([
            'sprint_id' => $sprint?->id,
            'sprint' => $sprint?->name,
        ]);

        try {
            ProjectTaskUpdated::dispatch($task->fresh(), 'sprint');
        } catch (\Throwable $e) {
            Log::warning('Realtime sprint broadcast failed: ' . $e->getMessage());
        }

        $message = $sprint
            ? "Đã đưa công việc \"{$task->name}\" vào Sprint {$sprint->name}."
            : "Đã trả công việc \"{$task->name}\" về Backlog.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'task' => $task->fresh(['assignee']),
                'sprint' => $sprint,
            ]);
        }

        return back()->with('success', $message);
    }

    /** Update Story Points (Planning Poker / Complexity estimation) */
    public function updateStoryPoints(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'story_points' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ]);

        $task->update(['story_points' => $data['story_points']]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'story_points' => $task->story_points,
                'message' => 'Cập nhật Story Points thành công.',
            ]);
        }

        return back()->with('success', 'Đã cập nhật Story Points.');
    }

    /** Quick create task directly into Backlog or a Sprint */
    public function quickStoreTask(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'issue_type' => ['nullable', 'in:task,bug,story,epic,subtask'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent,Thấp,Trung bình,Cao,Khẩn cấp'],
            'story_points' => ['nullable', 'numeric', 'min:0'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'sprint_id' => ['nullable', 'integer', 'exists:sprints,id'],
        ]);

        $issueCount = Task::where('project_id', $project->id)->count() + 1;
        $code = $project->project_code ?: 'TP';
        $issueKey = $code . '-' . $issueCount;

        $task = Task::create([
            'project_id' => $project->id,
            'company_id' => $project->company_id,
            'user_id' => Auth::id() ?: 1,
            'name' => $data['name'],
            'issue_key' => $issueKey,
            'issue_type' => $data['issue_type'] ?? 'task',
            'priority' => $data['priority'] ?? 'Trung bình',
            'status' => 'todo',
            'story_points' => $data['story_points'] ?? null,
            'assignee_id' => $data['assignee_id'] ?? null,
            'sprint_id' => $data['sprint_id'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'task' => $task->load('assignee'),
                'message' => "Đã tạo công việc \"{$task->name}\" thành công.",
            ]);
        }

        return back()->with('success', "Đã tạo công việc \"{$task->name}\" thành công.");
    }
}

