@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/edit-shared.css') }}?v={{ filemtime(public_path('build/assets/edit-shared.css')) }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
<div id="project-create-app" class="project-create-form"></div>
@endsection

@section('jqueryScript')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    window.projectCreateData = {
        companies: @json($companies ?? []),
        categories: @json($categories ?? []),
        teams: @json($teams ?? []),
        project_code_preview: @json($project_code_preview ?? ''),
        company_id: @json($company_id ?? null),
        errors: @json($errors ?? []),
        old: @json(old()),
        store_url: @json(route('projects.store')),
        category_store_url: @json(route('projects.categories.store')),
        company_store_url: @json(route('projects.companies.store')),
        csrf_token: @json(csrf_token()),
        projects_index_url: @json(route('projects.index')),
    };
</script>
<script type="module" src="{{ asset('build/assets/project-create.js') }}?v={{ filemtime(public_path('build/assets/project-create.js')) }}"></script>
@endsection
