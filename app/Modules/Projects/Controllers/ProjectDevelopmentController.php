<?php

namespace ProjectsManager\Modules\Projects\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Project;
use ProjectsManager\ProjectDevelopmentLink;

class ProjectDevelopmentController extends Controller
{
    public function index(Project $project): View
    {
        $project->load(['developmentLinks.user', 'tasks' => fn ($q) => $q->whereIn('status', ['in_progress', 'review'])->with('assignee')]);

        $linksByType = $project->developmentLinks->groupBy('type');

        return view('projects.development.index', compact('project', 'linksByType'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'type'   => ['required', 'string', 'in:branch,commit,pr,pipeline,deployment'],
            'label'  => ['required', 'string', 'max:255'],
            'url'    => ['required', 'url', 'max:2048'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $data['user_id']   = Auth::id();
        $data['linked_at'] = now();
        $project->developmentLinks()->create($data);

        return back()->with('success', 'Đã thêm liên kết phát triển.');
    }

    public function destroy(Project $project, ProjectDevelopmentLink $link): RedirectResponse
    {
        abort_unless($link->project_id === $project->id, 404);
        $link->delete();

        return back()->with('success', 'Đã xóa liên kết.');
    }
}
