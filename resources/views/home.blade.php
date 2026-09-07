@extends('layouts.app')

@section('content')
<section class="tp-page-head">
    <div>
        <span class="tp-eyebrow">TASKPILOT WORKSPACE</span>
        <h1>Chào {{ auth()->user()->name }}</h1>
        <p>Tổng quan công việc và không gian cộng tác của bạn.</p>
    </div>
    <div class="tp-actions">
        <a href="{{ route('tasks.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Tạo công việc</a>
        <a href="{{ route('projects.create') }}" class="btn btn-default"><i class="fa fa-folder-open-o"></i> Dự án mới</a>
    </div>
</section>
<section class="tp-stat-grid">
    <article class="tp-surface tp-stat"><i class="fa fa-folder-o"></i><span class="tp-stat-label">Dự án</span><strong>{{ $totalProjects }}</strong></article>
    <article class="tp-surface tp-stat"><i class="fa fa-check-square-o"></i><span class="tp-stat-label">Công việc</span><strong>{{ $totalTasks }}</strong></article>
    <article class="tp-surface tp-stat"><i class="fa fa-building-o"></i><span class="tp-stat-label">Khách hàng</span><strong>{{ $totalCompanies }}</strong></article>
    <article class="tp-surface tp-stat"><i class="fa fa-user-circle-o"></i><span class="tp-stat-label">Được giao cho bạn</span><strong>{{ $userTasks }}</strong></article>
</section>
<section class="row">
    <div class="col-md-8">
        <div class="tp-surface">
            <div class="tp-surface-header"><strong>Khởi động nhanh</strong></div>
            <div class="tp-surface-body">
                <p class="text-muted">Quản lý công việc theo dự án, theo dõi tiến độ bằng Board và tập trung vào việc quan trọng nhất.</p>
                <div class="tp-actions">
                    <a class="btn btn-primary" href="{{ route('projects.index') }}">Mở danh sách dự án</a>
                    <a class="btn btn-default" href="{{ route('tasks.index') }}">Mở bảng công việc</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tp-surface">
            <div class="tp-surface-header"><strong><i class="fa fa-magic" style="color:#8b5cf6;"></i> AI gợi ý hôm nay</strong></div>
            <div class="tp-surface-body">
                @if(($unassignedTasks ?? collect())->isEmpty())
                    <p class="text-muted mb-0">Không còn công việc nào chưa phân công. Tạo sprint từ dự án để gom backlog và theo dõi mục tiêu phát hành.</p>
                    <a href="{{ route('projects.index') }}" class="btn btn-link">Xem dự án <i class="fa fa-arrow-right"></i></a>
                @else
                    <p class="text-muted" style="margin-bottom: 10px;">Các công việc chưa có người nhận. Bấm AI để đề xuất người xử lý phù hợp.</p>
                    <div class="ai-home-list">
                        @foreach($unassignedTasks as $task)
                            <div class="ai-home-item">
                                <div style="min-width: 0;">
                                    <a href="{{ route('tasks.show', $task) }}" class="ai-home-item-title text-decoration-none">{{ $task->name }}</a>
                                    <div class="ai-home-item-meta">{{ $task->project->name ?? 'Dự án' }} · {{ strtoupper($task->priority ?? 'medium') }}</div>
                                </div>
                                @include('partials.ai_assignee_button', ['task' => $task, 'mode' => 'save', 'compact' => true])
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@section('jqueryScript')
@include('partials.ai_assignee_assets')
@endsection
