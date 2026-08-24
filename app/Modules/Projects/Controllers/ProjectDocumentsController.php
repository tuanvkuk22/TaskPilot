<?php

namespace ProjectsManager\Modules\Projects\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Project;
use ProjectsManager\ProjectDocument;

class ProjectDocumentsController extends Controller
{
    public function index(Project $project): View
    {
        $project->load(['documents.user']);

        return view('projects.documents.index', compact('project'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'url'         => ['required', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type'        => ['nullable', 'string', 'in:link,google_doc,notion,confluence,file'],
        ]);

        $data['user_id'] = Auth::id();
        $project->documents()->create($data);

        return back()->with('success', 'Đã thêm tài liệu.');
    }

    public function destroy(Project $project, ProjectDocument $document): RedirectResponse
    {
        abort_unless($document->project_id === $project->id, 404);
        $document->delete();

        return back()->with('success', 'Đã xóa tài liệu.');
    }
}
