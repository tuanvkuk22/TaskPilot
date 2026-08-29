@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<div id="task-index-app" class="task-index-container"></div>
@endsection

@section('jqueryScript')
<script>
    window.taskListData = {
        tasks: @json($tasks ?? []),
        projects: @json($projects ?? []),
        stats: @json($stats ?? []),
        create_task_url: @json(route('tasks.create')),
        selected_project: @json($selectedProject ?? ''),
        selected_status: @json($selectedStatus ?? ''),
        selected_priority: @json($selectedPriority ?? ''),
    };
</script>
<script type="module" src="{{ asset('build/assets/task-index.js') }}?v={{ file_exists(public_path('build/assets/task-index.js')) ? filemtime(public_path('build/assets/task-index.js')) : time() }}"></script>
@endsection