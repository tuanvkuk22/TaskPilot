@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/edit-shared.css') }}?v={{ file_exists(public_path('build/assets/edit-shared.css')) ? filemtime(public_path('build/assets/edit-shared.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('build/assets/task-create.css') }}?v={{ file_exists(public_path('build/assets/task-create.css')) ? filemtime(public_path('build/assets/task-create.css')) : time() }}" rel="stylesheet">
<div id="task-create-app" class="task-create-form"></div>
@endsection

@section('jqueryScript')
<script>
    window.taskCreateData = {
        projects: @json($projects ?? []),
        users: @json($users ?? []),
        reporters: @json($reporters ?? []),
        epics: @json($epics ?? []),
        sprints: @json($sprints ?? []),
        available_labels: @json($available_labels ?? []),
        project_id: @json($project_id ?? null),
        default_status: @json($default_status ?? null),
        effective_workflow: @json($effective_workflow ?? null),
        active_statuses: @json(collect($effective_workflow['statuses'] ?? [])->filter(fn($s) => ($s['is_active'] ?? true) !== false)->values()),
        old: @json(old()),
        errors: @json($errors ?? []),
        old_labels: @json(old('labels')),
        csrf_token: @json(csrf_token()),
        store_url: @json(route('tasks.store')),
        form_data_url: @json(route('tasks.form-data')),
        recommend_url: @json(route('tasks.recommend-assignee')),
        tasks_index_url: @json(route('tasks.index')),
    };
</script>
<script type="module" src="{{ asset('build/assets/task-create.js') }}?v={{ file_exists(public_path('build/assets/task-create.js')) ? filemtime(public_path('build/assets/task-create.js')) : time() }}"></script>
@endsection
