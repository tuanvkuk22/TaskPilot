@extends('layouts.app')
@section('content')
<link href="{{ asset('build/assets/project-list.css') }}?v={{ file_exists(public_path('build/assets/project-list.css')) ? filemtime(public_path('build/assets/project-list.css')) : time() }}" rel="stylesheet">
<div id="project-list-app"></div>
@endsection

@section('jqueryScript')
<script>
    window.projectListData = {
        projects: @json($projects),
        stats: @json($stats),
    };
</script>
<script type="module" src="{{ asset('build/assets/project-list.js') }}"></script>
@endsection
