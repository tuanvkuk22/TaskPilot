<?php

namespace ProjectsManager\Modules\Projects\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Project;
use ProjectsManager\ProjectForm;
use ProjectsManager\ProjectFormResponse;

class ProjectFormsController extends Controller
{
    public function index(Project $project): View
    {
        $project->load(['forms.user', 'forms.responses']);

        return view('projects.forms.index', compact('project'));
    }

    public function storeForm(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'fields'      => ['required', 'array', 'min:1'],
            'fields.*.label'    => ['required', 'string', 'max:255'],
            'fields.*.type'     => ['required', 'string', 'in:text,textarea,number,date,select,checkbox,radio'],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.options'  => ['nullable', 'string'],
        ]);

        $data['user_id'] = Auth::id();
        $project->forms()->create($data);

        return back()->with('success', 'Đã tạo form.');
    }

    public function show(Project $project, ProjectForm $form): View
    {
        abort_unless($form->project_id === $project->id, 404);
        $form->load(['responses.user']);

        return view('projects.forms.show', compact('project', 'form'));
    }

    public function submitResponse(Request $request, Project $project, ProjectForm $form): RedirectResponse
    {
        abort_unless($form->project_id === $project->id, 404);

        $responseData = [];
        foreach ($form->fields as $field) {
            $responseData[$field['label']] = $request->input('field_' . \Str::slug($field['label']));
        }

        $form->responses()->create([
            'user_id' => Auth::id(),
            'data'    => $responseData,
        ]);

        return back()->with('success', 'Đã gửi câu trả lời.');
    }

    public function destroyForm(Project $project, ProjectForm $form): RedirectResponse
    {
        abort_unless($form->project_id === $project->id, 404);
        $form->delete();

        return back()->with('success', 'Đã xóa form.');
    }
}
