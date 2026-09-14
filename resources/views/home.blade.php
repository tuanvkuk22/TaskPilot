@extends('layouts.app')

@push('styles')
@if(file_exists(public_path('build/assets/home.css')))
    <link href="{{ asset('build/assets/home.css') }}?v={{ filemtime(public_path('build/assets/home.css')) }}" rel="stylesheet">
@endif
@endpush

@section('content')
<div class="col-xs-12">
    <div id="home-app"></div>
</div>
@endsection

@push('scripts')
<script>
window.homeData = {
    user_name: @json(auth()->user()->name),
    total_projects: @json($totalProjects),
    total_tasks: @json($totalTasks),
    total_companies: @json($totalCompanies),
    user_tasks: @json($userTasks),
    unassigned_tasks: @json($unassignedTasks),
    routes: {
        tasksCreate: @json(route('tasks.create')),
        projectsCreate: @json(route('projects.create')),
        projectsIndex: @json(route('projects.index')),
        tasksIndex: @json(route('tasks.index')),
        teamsIndex: @json(route('teams.index')),
        companiesIndex: @json(route('companies.index')),
        skillsIndex: @json(route('skills.index')),
        guide: @json(route('guide'))
    }
};
</script>
@if(file_exists(public_path('build/assets/home.js')))
    <script type="module" src="{{ asset('build/assets/home.js') }}?v={{ filemtime(public_path('build/assets/home.js')) }}"></script>
@endif
@endpush

@section('jqueryScript')
@include('partials.ai_assignee_assets')
@endsection
