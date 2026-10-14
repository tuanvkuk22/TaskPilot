@extends('layouts.app')

@push('styles')
@if(file_exists(public_path('build/assets/user-profile.css')))
    <link href="{{ asset('build/assets/user-profile.css') }}?v={{ filemtime(public_path('build/assets/user-profile.css')) }}" rel="stylesheet">
@endif
@endpush

@section('content')
<div class="col-xs-12">
    <div id="user-profile-app"></div>
</div>
@endsection

@push('scripts')
<script>
window.profileData = {
    user: @json($user),
    stats: @json($stats),
    initialSkills: @json($initialSkills),
    matchingTasks: @json($matchingTasks)
};
</script>
@if(file_exists(public_path('build/assets/user-profile.js')))
    <script type="module" src="{{ asset('build/assets/user-profile.js') }}?v={{ filemtime(public_path('build/assets/user-profile.js')) }}"></script>
@endif
@endpush
