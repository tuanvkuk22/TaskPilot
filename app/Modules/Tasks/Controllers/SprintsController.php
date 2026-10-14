<?php

namespace ProjectsManager\Modules\Tasks\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
        $project->load(['sprints' => fn ($query) => $query
            ->with(['tasks' => fn ($tasks) => $tasks->with('assignee')->orderByDesc('priority')->orderBy('name')])
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'planned' THEN 1 ELSE 2 END")
            ->orderBy('starts_at')]);

        return view('sprints.index', [
            'project' => $project,
            'backlog' => $project->tasks()->whereNull('sprint_id')->with('assignee')->orderByDesc('priority')->get(),
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

        $project->sprints()->create($data);

        return back()->with('success', 'Sprint đã được tạo.');
    }

    public function start(Project $project, Sprint $sprint): RedirectResponse
    {
        abort_unless($sprint->project_id === $project->id, 404);

        $sprint->project->sprints()->where('status', 'active')->update(['status' => 'closed']);
        $sprint->update(['status' => 'active', 'starts_at' => $sprint->starts_at ?: now()->toDateString()]);

        return back()->with('success', 'Sprint đã bắt đầu.');
    }

    public function close(Project $project, Sprint $sprint): RedirectResponse
    {
        abort_unless($sprint->project_id === $project->id, 404);

        $sprint->update(['status' => 'closed']);

        return back()->with('success', 'Đã đóng Sprint.');
    }

    /** Move a project task into a sprint, or return it to the backlog. */
    public function moveTask(Request $request, Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        $data = $request->validate(['sprint_id' => ['nullable', 'integer', 'exists:sprints,id']]);
        $sprint = empty($data['sprint_id']) ? null : $project->sprints()->findOrFail($data['sprint_id']);

        $task->update([
            'sprint_id' => $sprint?->id,
            // Keep the legacy field in sync for older task views and imports.
            'sprint' => $sprint?->name,
        ]);

        try {
            ProjectTaskUpdated::dispatch($task->fresh(), 'sprint');
        } catch (\Throwable $exception) {
            Log::warning('Realtime sprint broadcast failed after a persisted update.', [
                'task_id' => $task->id,
                'exception' => $exception::class,
            ]);
        }

        return back()->with('success', $sprint
            ? "Đã đưa {$task->name} vào {$sprint->name}."
            : "Đã trả {$task->name} về Backlog.");
    }
}
