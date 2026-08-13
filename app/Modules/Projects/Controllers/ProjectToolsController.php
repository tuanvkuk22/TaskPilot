<?php

namespace ProjectsManager\Modules\Projects\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Project;

class ProjectToolsController extends Controller
{
    public function documents(Project $project): View { return view('projects.tools', compact('project'))->with('tool', 'documents'); }
    public function budgets(Project $project): View { return view('projects.tools', compact('project'))->with('tool', 'budgets'); }
    public function chat(Project $project): View { return view('projects.tools', compact('project'))->with('tool', 'chat'); }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $tool = $request->input('tool');
        $rules = match ($tool) {
            'documents' => ['title' => ['required', 'string', 'max:255'], 'url' => ['required', 'url'], 'description' => ['nullable', 'string']],
            'meetings' => ['title' => ['required', 'string', 'max:255'], 'platform' => ['required', 'in:meet,google_meet,jitsi,agora'], 'url' => ['required', 'url'], 'starts_at' => ['nullable', 'date']],
            'budgets' => ['title' => ['required', 'string', 'max:255'], 'amount' => ['required', 'numeric', 'min:0'], 'kind' => ['required', 'in:income,expense']],
            'chat' => ['body' => ['required', 'string', 'max:2000']],
            default => [],
        };
        $data = $request->validate($rules);
        $project->comments()->create(['user_id' => auth()->id(), 'body' => $tool === 'chat' ? $data['body'] : json_encode($data)]);

        return back()->with('success', 'Đã lưu thông tin.');
    }
}