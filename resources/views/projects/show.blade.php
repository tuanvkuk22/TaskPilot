@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('css/project-dashboard-luxury.css') }}?v={{ file_exists(public_path('css/project-dashboard-luxury.css')) ? filemtime(public_path('css/project-dashboard-luxury.css')) : time() }}" rel="stylesheet">
@if(file_exists(public_path('build/assets/project-dashboard.css')))
<link href="{{ asset('build/assets/project-dashboard.css') }}?v={{ filemtime(public_path('build/assets/project-dashboard.css')) }}" rel="stylesheet">
@endif

<div id="project-dashboard-app"></div>
@endsection

@section('jqueryScript')
<script>
    window.projectDashboardData = {
        project: @json($project),
        tasks: @json($project->tasks),
        comments: @json($comments),
        activities: @json($activities),
    };
</script>
<script type="module" src="{{ asset('build/assets/project-dashboard.js') }}?v={{ file_exists(public_path('build/assets/project-dashboard.js')) ? filemtime(public_path('build/assets/project-dashboard.js')) : time() }}"></script>
@endsection
