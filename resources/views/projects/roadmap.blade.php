@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}">
<link rel="stylesheet" href="{{ asset('build/assets/edit-shared.css') }}?v={{ file_exists(public_path('build/assets/edit-shared.css')) ? filemtime(public_path('build/assets/edit-shared.css')) : time() }}">
<link rel="stylesheet" href="{{ asset('build/assets/project-roadmap.css') }}?v={{ file_exists(public_path('build/assets/project-roadmap.css')) ? filemtime(public_path('build/assets/project-roadmap.css')) : time() }}">

<style>
    /* Reset negative row margins for calendar container */
    .tp-calendar-page-container {
        width: 100%;
        max-width: 100%;
        padding: 0;
        margin: 0;
    }
</style>

<div class="tp-calendar-page-container">
    <div id="roadmap-calendar-app"></div>
</div>
@endsection

@section('jqueryScript')
<script>
    window.roadmapCalendarData = {
        project: @json($project),
        effective_workflow: @json($effective_workflow ?? null),
        tasks: @json($project->tasks ?? []),
        sprints: @json($project->sprints ?? []),
        users: @json($project->users ?? []),
    };
</script>
<script type="module" src="{{ asset('build/assets/project-roadmap.js') }}?v={{ file_exists(public_path('build/assets/project-roadmap.js')) ? filemtime(public_path('build/assets/project-roadmap.js')) : time() }}"></script>
@endsection

