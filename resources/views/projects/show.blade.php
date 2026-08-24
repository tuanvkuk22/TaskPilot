@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/project-dashboard.css') }}?v={{ filemtime(public_path('build/assets/project-dashboard.css')) }}" rel="stylesheet">
<div id="project-dashboard-app"></div>
@endsection

@section('jqueryScript')
<script>
    window.projectDashboardData = {
        project: @json($project),
        tasks: @json($project->tasks),
        comments: @json($comments),
    };
</script>
<script type="module" src="{{ asset('build/assets/project-dashboard.js') }}"></script>
@endsection
