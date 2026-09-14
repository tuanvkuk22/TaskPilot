@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}">

<div class="project-dashboard-vue container-fluid py-3" style="max-width: 1400px;">
    <div class="pd-header mb-3">
        <div class="pd-header-left">
            <div class="pd-breadcrumb mb-1">
                <a href="{{ route('projects.index') }}">Dự án</a><span>/</span><span>{{ $project->name }}</span><span>/</span><span class="text-dark font-weight-bold">Roadmap</span>
            </div>
            <div class="pd-title-row">
                <h1 class="pd-title m-0">{{ $project->name }}</h1>
                <span class="pd-project-key">{{ $project->project_code ?? 'PROJ' }}</span>
                <span class="pd-project-status">{{ strtoupper($project->status ?? 'active') }}</span>
                <a href="{{ route('projects.edit', $project) }}" class="pd-settings-btn" title="Cài đặt dự án"><i class="fa fa-cog"></i></a>
            </div>
        </div>
        <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="tp-btn tp-btn-primary"><i class="fa fa-plus-circle" style="color: #ffffff !important;"></i><span>Tạo công việc mới</span></a>
    </div>

    <div class="pd-project-nav mb-4">
        <a href="{{ route('projects.index') }}" class="pd-nav-item">Dự án</a>
        <a href="{{ route('projects.show', $project) }}" class="pd-nav-item">Tổng quan</a>
        <a href="{{ route('projects.board', $project) }}" class="pd-nav-item">Bảng</a>
        <a href="{{ route('projects.members.index', $project) }}" class="pd-nav-item">Thành viên</a>
        <a href="{{ route('projects.roles.index', $project) }}" class="pd-nav-item">Vai trò &amp; Quyền</a>
        <a href="{{ route('sprints.index', $project) }}" class="pd-nav-item">Backlog / Sprint</a>
        <a href="{{ route('projects.timeline', $project) }}" class="pd-nav-item">Timeline</a>
        <a href="{{ route('projects.roadmap', $project) }}" class="pd-nav-item active">Roadmap</a>
    </div>

    <section class="tp-roadmap-surface">
        <div class="tp-roadmap-heading">
            <div><span class="tp-roadmap-eyebrow"><i class="fa fa-map-signs"></i> LỘ TRÌNH DỰ ÁN</span><h2>Epic và công việc theo kế hoạch</h2><p>Theo dõi tiến độ, sprint và hạn hoàn thành của từng hạng mục.</p></div>
            <span class="tp-roadmap-count">{{ $project->tasks->count() }} công việc</span>
        </div>
        <div class="tp-roadmap-table-wrap">
            <div class="tp-roadmap-table-header"><span>Hạng mục</span><span>Tiến độ</span><span>Sprint</span><span>Hạn hoàn thành</span></div>
            @forelse($project->tasks->sortByDesc(fn ($task) => $task->issue_type === 'epic') as $task)
                @php
                    $progress = $task->status === 'done' ? 100 : ($task->status === 'in_progress' ? 50 : 0);
                    $statusLabel = $task->status === 'done' ? 'Hoàn thành' : ($task->status === 'in_progress' ? 'Đang làm' : 'Sẽ làm');
                @endphp
                <article class="tp-roadmap-row">
                    <div class="tp-roadmap-item"><span class="tp-roadmap-type type-{{ $task->issue_type ?? 'task' }}">{{ strtoupper($task->issue_type ?? 'task') }}</span><div><a href="{{ route('tasks.show', $task) }}">{{ $task->name }}</a><span>{{ $task->issue_key ?? 'TP-' . str_pad($task->id, 5, '0', STR_PAD_LEFT) }}</span></div></div>
                    <div class="tp-roadmap-progress-cell"><div class="tp-roadmap-progress"><span style="width: {{ $progress }}%"></span></div><small>{{ $progress }}% · {{ $statusLabel }}</small></div>
                    <div class="tp-roadmap-sprint">{{ $task->sprint ?: 'Backlog' }}</div>
                    <time class="tp-roadmap-date">{{ optional($task->due_date ?? $task->end_date)->format('d/m/Y') ?? 'Chưa đặt hạn' }}</time>
                </article>
            @empty
                <div class="tp-roadmap-empty"><i class="fa fa-map-o"></i><strong>Roadmap chưa có công việc</strong><span>Tạo công việc đầu tiên để bắt đầu theo dõi lộ trình dự án.</span><a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="tp-btn tp-btn-primary">Tạo công việc mới</a></div>
            @endforelse
        </div>
    </section>
</div>
@endsection
