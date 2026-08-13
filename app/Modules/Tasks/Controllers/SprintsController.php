<?php

namespace ProjectsManager\Modules\Tasks\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Project;
use ProjectsManager\Sprint;

class SprintsController extends Controller
{
    public function index(Project $project): View
    {
        $project->load(['sprints.tasks']);

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

    public function start(Sprint $sprint): RedirectResponse
    {
        $sprint->project->sprints()->where('status', 'active')->update(['status' => 'closed']);
        $sprint->update(['status' => 'active', 'starts_at' => $sprint->starts_at ?: now()->toDateString()]);

        return back()->with('success', 'Sprint đã bắt đầu.');
    }

    public function close(Sprint $sprint): RedirectResponse
    {
        $sprint->update(['status' => 'closed']);

        return back()->with('success', 'Sprint đã đóng.');
    }
}
