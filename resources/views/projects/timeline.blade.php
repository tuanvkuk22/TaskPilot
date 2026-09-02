@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}">
<link rel="stylesheet" href="{{ asset('build/assets/project-timeline.css') }}?v={{ file_exists(public_path('build/assets/project-timeline.css')) ? filemtime(public_path('build/assets/project-timeline.css')) : time() }}">

<!-- Local Open-Source CKEditor 4.22.1 -->
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>

<div class="container-fluid py-3 px-4" style="width: 100%; max-width: 100%;">
    <div id="project-timeline-app"></div>
</div>

<script>
    window.projectTimelineData = {
        project: @json($project),
        tasks: @json($tasks),
        sprints: @json($sprints ?? [])
    };
</script>

<script type="module" src="{{ asset('build/assets/project-timeline.js') }}?v={{ file_exists(public_path('build/assets/project-timeline.js')) ? filemtime(public_path('build/assets/project-timeline.js')) : time() }}"></script>
@endsection
