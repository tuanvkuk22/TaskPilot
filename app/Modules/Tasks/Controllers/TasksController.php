<?php

namespace ProjectsManager\Modules\Tasks\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Modules\Tasks\Handlers\CreateTaskHandler;
use ProjectsManager\Modules\Tasks\Handlers\DeleteTaskHandler;
use ProjectsManager\Modules\Tasks\Handlers\ListTasksHandler;
use ProjectsManager\Modules\Tasks\Handlers\ShowTaskHandler;
use ProjectsManager\Modules\Tasks\Handlers\StoreTaskHandler;
use ProjectsManager\Modules\Tasks\Handlers\UpdateTaskHandler;
use ProjectsManager\Task;
use ProjectsManager\Worklog;

class TasksController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ListTasksHandler $listTasksHandler
     * @param CreateTaskHandler $createTaskHandler
     * @param StoreTaskHandler $storeTaskHandler
     * @param ShowTaskHandler $showTaskHandler
     * @param UpdateTaskHandler $updateTaskHandler
     * @param DeleteTaskHandler $deleteTaskHandler
     */
    public function __construct(
        private readonly ListTasksHandler $listTasksHandler,
        private readonly CreateTaskHandler $createTaskHandler,
        private readonly StoreTaskHandler $storeTaskHandler,
        private readonly ShowTaskHandler $showTaskHandler,
        private readonly UpdateTaskHandler $updateTaskHandler,
        private readonly DeleteTaskHandler $deleteTaskHandler
    ) {
    }

    /**
     * Display a listing of tasks.
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $data = $this->listTasksHandler->handle(
            Auth::id(),
            $request->query('project_id'),
            $request->query('status'),
            $request->query('priority')
        );

        return view('tasks.index', $data);
    }

    /**
     * Show the form for creating a new task.
     *
     * @param Request $request
     *
     * @return View
     */
    public function create(Request $request): View
    {
        $data = $this->createTaskHandler->handle(Auth::id(), $request->query('project_id'));
        $status = $request->query('status');
        
        $activeStatuses = collect($data['effective_workflow']['statuses'] ?? [])
            ->filter(fn ($s) => ($s['is_active'] ?? true) !== false);
        $validKeys = $activeStatuses->pluck('key')->toArray();

        $defaultInitial = $activeStatuses->firstWhere('is_initial', true)['key']
            ?? $activeStatuses->first()['key']
            ?? 'todo';

        $data['default_status'] = in_array($status, $validKeys, true)
            ? $status
            : $defaultInitial;

        return view('tasks.create', $data);
    }

    /**
     * Return project-specific options for the task creation form.
     */
    public function formData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        $projectId = (int) $validated['project_id'];
        $data = $this->createTaskHandler->handle(Auth::id(), $projectId);

        abort_unless($data['projects']->contains('id', $projectId), 404);

        $activeStatuses = collect($data['effective_workflow']['statuses'] ?? [])
            ->filter(fn ($s) => ($s['is_active'] ?? true) !== false)
            ->values();

        return response()->json([
            'users' => $data['users']->values(),
            'reporters' => $data['reporters']->values(),
            'epics' => $data['epics']->values(),
            'sprints' => $data['sprints']->values(),
            'effective_workflow' => $data['effective_workflow'] ?? null,
            'statuses' => $activeStatuses,
        ]);
    }

    /**
     * Store a newly created task in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'issue_type' => ['required', 'in:task,bug,story,epic,subtask'],
            'status' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'reporter_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'story_points' => ['nullable', 'numeric', 'min:0'],
            'labels' => ['nullable', 'string', 'max:1000'],
            'sprint_id' => ['nullable', 'integer', 'exists:sprints,id'],
            'parent_id' => ['nullable', 'integer', 'exists:tasks,id'],
        ]);

        $project = \ProjectsManager\Project::find($request->project_id);
        if ($project && $request->filled('status')) {
            $activeStatusKeys = collect($project->getActiveStatuses())->pluck('key')->toArray();
            if (!in_array($request->status, $activeStatusKeys, true)) {
                $msg = 'Trạng thái được chọn không hợp lệ hoặc đã bị vô hiệu hóa trong quy trình của dự án.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                        'errors' => ['status' => [$msg]],
                    ], 422);
                }
                return back()->withInput()->withErrors(['status' => $msg]);
            }
        }

        $task = $this->storeTaskHandler->handle(Auth::id(), $request->all());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã tạo công việc thành công.',
                'task' => $task->fresh(['project', 'assignee', 'reporter', 'parent'])
            ]);
        }

        return redirect()->route('tasks.index', ['project_id' => $task->project_id])
            ->with('task_success', 'Đã tạo công việc thành công.');
    }

    /**
     * Display the specified task.
     *
     * @param Task $task
     *
     * @return View
     */
    public function show(Task $task): View
    {
        $task = $this->showTaskHandler->handle($task);
        $formData = $this->createTaskHandler->handle(Auth::id(), $task->project_id);
        $effectiveWorkflow = $task->project ? $task->project->effective_workflow : \ProjectsManager\Project::defaultWorkflowForType('software');
        $activeStatuses = $task->project ? $task->project->getActiveStatuses() : ($effectiveWorkflow['statuses'] ?? []);

        return view('tasks.show', array_merge($formData, [
            'task' => $task,
            'effective_workflow' => $effectiveWorkflow,
            'active_statuses' => $activeStatuses,
            'worklogs' => $task->worklogs()->with(['user', 'assignee'])->latest('logged_at')->get(),
            'comments' => $task->comments()->with('user')->latest()->get(),
        ]));
    }

    public function worklog(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate([
            'hours' => ['required', 'numeric', 'min:0.1', 'max:24'],
            'logged_at' => ['required', 'date'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $assigneeId = $data['assignee_id'] ?? Auth::id();

        if (!empty($data['started_at']) && !empty($data['ended_at'])) {
            $data['hours'] = round((strtotime($data['ended_at']) - strtotime($data['started_at'])) / 3600, 2);
        }

        unset($data['started_at'], $data['ended_at']);

        $task->worklogs()->create($data + ['user_id' => Auth::id(), 'assignee_id' => $assigneeId]);

        return back()->with('success', 'Worklog đã được ghi nhận.');
    }

    /**
     * Show the form for editing the specified task.
     *
     * @param Task $task
     *
     * @return View
     */
    public function edit(Task $task): View
    {
        $data = $this->createTaskHandler->handle(Auth::id(), $task->project_id);
        $data['task'] = $task;

        return view('tasks.edit', $data);
    }

    /**
     * Update the specified task in storage.
     *
     * @param Request $request
     * @param Task $task
     *
     * @return RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Task $task): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'issue_type' => ['sometimes', 'required', 'in:task,bug,story,epic,subtask'],
            'status' => ['sometimes', 'required', 'string', 'max:100'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'reporter_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'story_points' => ['nullable', 'numeric', 'min:0'],
            'labels' => ['nullable', 'string', 'max:1000'],
            'sprint_id' => ['nullable', 'integer', 'exists:sprints,id'],
            'parent_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->has('status') && $request->input('status') !== $task->status) {
            $gateError = $this->validateWorkflowGates($task, $request->input('status'), $request);
            if ($gateError) {
                $statusCode = $gateError['status_code'] ?? 422;
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $gateError['message'],
                        'gate_type' => $gateError['type'],
                    ], $statusCode);
                }
                return back()->withInput()->withErrors(['status' => $gateError['message']]);
            }
        }

        $this->updateTaskHandler->handle($task, $request->all());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'task' => $task->fresh(['project', 'assignee', 'reporter', 'parent', 'worklogs', 'approver'])]);
        }

        if ($request->has('redirect_to_show') || url()->previous() == route('tasks.show', $task->id)) {
            return redirect()->route('tasks.show', $task->id)->with('success', 'Công việc đã được cập nhật.');
        }

        return redirect()->route('tasks.index', ['project_id' => $task->project_id])
            ->with('success', 'Task updated successfully.');
    }

    public function move(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:100'],
            'approval_signature' => ['nullable', 'string', 'max:255'],
            'approval_role' => ['nullable', 'string', 'max:50'],
            'worklog_hours' => ['nullable', 'numeric', 'min:0.1', 'max:24'],
            'worklog_description' => ['nullable', 'string', 'max:1000'],
            'test_status' => ['nullable', 'string', 'in:passed,failed,not_tested'],
            'test_result' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['status'] !== $task->status) {
            $gateError = $this->validateWorkflowGates($task, $data['status'], $request);
            if ($gateError) {
                $statusCode = $gateError['status_code'] ?? 422;
                return response()->json([
                    'success' => false,
                    'message' => $gateError['message'],
                    'gate_type' => $gateError['type'],
                    'required_action' => $gateError['required_action'],
                ], $statusCode);
            }
        }

        $this->updateTaskHandler->handle($task, ['status' => $data['status']]);

        return response()->json([
            'success' => true,
            'status' => $data['status'],
            'task' => $task->fresh(['project', 'assignee', 'reporter', 'parent', 'worklogs', 'approver']),
        ]);
    }

    public function approve(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'signature' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        $task->update([
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_signature' => $data['signature'],
            'approval_role' => $data['role'] ?? 'tech_lead',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã ký duyệt công việc thành công.',
            'task' => $task->fresh(['approver', 'worklogs']),
        ]);
    }

    public function saveTestResult(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'test_status' => ['required', 'string', 'in:passed,failed,not_tested'],
            'test_result' => ['required', 'string', 'max:2000'],
        ]);

        $task->update([
            'test_status' => $data['test_status'],
            'test_result' => $data['test_result'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã ghi nhận kết quả kiểm thử thành công.',
            'task' => $task->fresh(),
        ]);
    }

    protected function validateWorkflowGates(Task $task, string $targetStatus, Request $request): ?array
    {
        if ($task->project) {
            $check = $task->project->canTransitionTaskStatus($task, $targetStatus, $request->user());
            if (!($check['allowed'] ?? false)) {
                return [
                    'message' => $check['message'] ?? 'Chuyển đổi trạng thái không hợp lệ theo quy trình dự án.',
                    'type' => 'workflow_transition',
                    'required_action' => 'select_valid_status',
                    'status_code' => $check['status_code'] ?? 422,
                ];
            }
        }

        return null;
    }




    /**
     * Remove the specified task from storage.
     *
     * @param Task $task
     *
     * @return RedirectResponse
     */
    public function destroy(Task $task): RedirectResponse
    {
        $deleted = $this->deleteTaskHandler->handle($task);

        if (! $deleted) {
            return back()->with('error', 'Không thể xóa công việc. Vui lòng thử lại.');
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Đã xóa công việc.');
    }
}
