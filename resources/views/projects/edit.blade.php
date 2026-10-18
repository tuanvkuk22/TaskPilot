@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('build/assets/edit-shared.css') }}?v={{ file_exists(public_path('build/assets/edit-shared.css')) ? filemtime(public_path('build/assets/edit-shared.css')) : time() }}" rel="stylesheet">
@if(file_exists(public_path('build/assets/project-edit.css')))
<link href="{{ asset('build/assets/project-edit.css') }}?v={{ filemtime(public_path('build/assets/project-edit.css')) }}" rel="stylesheet">
@endif
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">

<div class="col-md-12 p-0">
    <div id="project-edit-app" class="project-edit-form" style="width: 100%; max-width: 1240px; margin: 0 auto;"></div>
</div>
@endsection

@section('jqueryScript')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    window.projectEditData = {
        project: @json($project),
        companies: @json($companies ?? []),
        categories: @json($categories ?? []),
        teams: @json($teams ?? []),
        errors: @json($errors ?? []),
        old: @json(old()),
        category_store_url: @json(route('projects.categories.store')),
        company_store_url: @json(route('projects.companies.store')),
        csrf_token: @json(csrf_token()),
        update_url: @json($update_url ?? route('projects.update', $project->id)),
        projects_index_url: @json(route('projects.index')),
        project_image_url: @json($project->image ? \Illuminate\Support\Facades\Storage::url($project->image) : ''),
        effective_workflow: @json($effective_workflow ?? null),
        default_workflows: @json($default_workflows ?? []),
    };
</script>
<script type="module" src="{{ asset('build/assets/project-edit.js') }}?v={{ filemtime(public_path('build/assets/project-edit.js')) }}"></script>
@endsection
