@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/project-members.css') }}" rel="stylesheet">
<div id="project-members-app"></div>
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
