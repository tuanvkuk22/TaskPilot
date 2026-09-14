@extends('layouts.app')

@push('styles')
@if(file_exists(public_path('build/assets/skills-profile.css')))
    <link href="{{ asset('build/assets/skills-profile.css') }}?v={{ filemtime(public_path('build/assets/skills-profile.css')) }}" rel="stylesheet">
@endif
@endpush

@section('content')
<div class="col-xs-12">
    <div id="skills-profile-app"></div>
</div>
@endsection

@push('scripts')
<script>
window.skillsData = {
    initialSkills: @json($initialSkills),
    matchingTasks: @json($matchingTasks)
};
</script>
@if(file_exists(public_path('build/assets/skills-profile.js')))
    <script type="module" src="{{ asset('build/assets/skills-profile.js') }}?v={{ filemtime(public_path('build/assets/skills-profile.js')) }}"></script>
@endif
@endpush
