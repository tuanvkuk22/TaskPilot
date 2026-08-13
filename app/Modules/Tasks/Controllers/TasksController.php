<?php

namespace ProjectsManager\Modules\Tasks\Controllers;

use Illuminate\Http\RedirectResponse;
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

        return view('tasks.create', $data);
    }

    /**
     * Store a newly created task in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'issue_type' => ['required', 'in:task,bug,story,epic,subtask'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'story_points' => ['nullable', 'numeric', 'min:0'],
            'labels' => ['nullable', 'string', 'max:1000'],
            'sprint' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'sprint_id' => ['nullable', 'integer', 'exists:sprints,id'],
        ]);

        $task = $this->storeTaskHandler->handle(Auth::id(), $request->all());

        return redirect()->route('tasks.index', ['project_id' => $task->project_id])
            ->with('success', 'Task created successfully.');
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

        return view('tasks.show', ['task' => $task, 'worklogs' => $task->worklogs()->with('user')->latest('logged_at')->get()]);
    }

    public function worklog(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate([
            'hours' => ['required', 'numeric', 'min:0.1', 'max:24'],
            'logged_at' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $task->worklogs()->create($data + ['user_id' => Auth::id()]);

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
     * @return RedirectResponse
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'issue_type' => ['required', 'in:task,bug,story,epic,subtask'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'story_points' => ['nullable', 'numeric', 'min:0'],
            'labels' => ['nullable', 'string', 'max:1000'],
            'sprint' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'sprint_id' => ['nullable', 'integer', 'exists:sprints,id'],
        ]);
        $this->updateTaskHandler->handle($task, $request->all());

        return redirect()->route('tasks.index', ['project_id' => $task->project_id])
            ->with('success', 'Task updated successfully.');
    }

    public function move(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:todo,in_progress,done'],
        ]);

        $this->updateTaskHandler->handle($task, ['status' => $data['status']]);

        return response()->json(['success' => true, 'status' => $data['status']]);
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
        $this->deleteTaskHandler->handle($task);

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
