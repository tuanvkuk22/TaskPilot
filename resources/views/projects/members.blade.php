@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/project-members.css') }}" rel="stylesheet">
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<div class="project-dashboard-vue container-fluid py-3" style="max-width: 1400px;">
    <div id="project-members-app"></div>
</div>
@endsection

@section('jqueryScript')
<script>
    window.projectMembersData = {
        project: @json($project),
        members: @json($project->users),
        roles: @json($project->roles),
        csrf_token: '{{ csrf_token() }}',
    };
</script>
<script type="module" src="{{ asset('build/assets/project-members.js') }}"></script>
@endsection
