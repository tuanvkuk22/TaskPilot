@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/project-roles.css') }}" rel="stylesheet">
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<div class="project-dashboard-vue container-fluid py-3" style="max-width: 1400px;">
<div id="project-roles-app">
    <div style="padding: 20px; color: #666;">
        Đang tải giao diện quản lý vai trò...
    </div>
</div>
</div>
@endsection

@section('jqueryScript')
<script>
    window.projectRolesData = {
        project: @json($project),
        roles: @json($roles),
        csrf_token: '{{ csrf_token() }}',
    };
</script>
<script>
    console.log('test script running');
    document.getElementById('project-roles-app').setAttribute('data-test', 'inline-ok');
</script>
<script type="module" src="{{ asset('build/assets/project-roles.js') }}"></script>
@endsection
