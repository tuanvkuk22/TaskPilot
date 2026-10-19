@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/project-dashboard.css') }}" rel="stylesheet">
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">

<div class="project-dashboard-vue container-fluid py-3" style="box-sizing: border-box;">
    <!-- Standard Project Header and Navigation in sticky panel -->
    <div class="tp-screen-sticky-panel">
        <div class="pd-header mb-3">
            <div class="pd-header-left">
                <div class="pd-breadcrumb mb-1">
                    <a href="{{ route('projects.index') }}">Dự án</a>
                    <span>/</span>
                    <span>{{ $project->name }}</span>
                    <span>/</span>
                    <span class="text-dark font-weight-bold">Bảng (Board)</span>
                </div>
                <div class="pd-title-row">
                    <h1 class="pd-title m-0">{{ $project->name }}</h1>
                    <span class="pd-project-key">{{ $project->project_code ?? 'PROJ' }}</span>
                    <span class="pd-project-status">{{ strtoupper($project->status ?? 'active') }}</span>
                    <a href="{{ route('projects.edit', $project) }}" class="pd-settings-btn" title="Cài đặt dự án">
                        <i class="fa fa-cog"></i>
                    </a>
                </div>
            </div>
            <div>
                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="tp-btn tp-btn-primary">
                    <i class="fa fa-plus-circle" style="color: #ffffff !important;"></i>
                    <span>Tạo công việc mới</span>
                </a>
            </div>
        </div>

        <!-- Standard Project Sub-Nav Bar -->
        <div class="pd-project-nav mb-4">
            <a href="{{ route('projects.index') }}" class="pd-nav-item">Dự án</a>
            <a href="{{ route('projects.show', $project) }}" class="pd-nav-item">Tổng quan</a>
            <a href="{{ route('projects.board', $project) }}" class="pd-nav-item active">Bảng</a>
            <a href="{{ route('projects.members.index', $project) }}" class="pd-nav-item">Thành viên</a>
            <a href="{{ route('projects.roles.index', $project) }}" class="pd-nav-item">Vai trò &amp; Quyền</a>
            <a href="{{ route('sprints.index', $project) }}" class="pd-nav-item">Backlog / Sprint</a>
            <a href="{{ route('projects.timeline', $project) }}" class="pd-nav-item">Timeline</a>
            <a href="{{ route('projects.roadmap', $project) }}" class="pd-nav-item">Lịch biểu</a>
        </div>
    </div>

    <!-- Vue TaskPilot Kanban App (Includes Live Stats + Filters + Board) -->
    <div id="task-index-app" class="task-index-container"></div>
</div>

@endsection

@section('jqueryScript')
<script>
    window.taskListData = {
        tasks: @json($tasks ?? []),
        projects: @json([$project]),
        stats: @json($stats ?? []),
        create_task_url: @json(route('tasks.create', ['project_id' => $project->id])),
        selected_project: @json($project->id),
        selected_status: '',
        selected_priority: '',
        effective_workflow: @json($effective_workflow ?? null),
    };
</script>
<script type="module" src="{{ asset('build/assets/task-index.js') }}?v={{ file_exists(public_path('build/assets/task-index.js')) ? filemtime(public_path('build/assets/task-index.js')) : time() }}"></script>
@endsection