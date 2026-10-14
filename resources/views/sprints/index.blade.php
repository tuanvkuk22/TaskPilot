@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/sprint-review.css') }}?v={{ filemtime(public_path('css/sprint-review.css')) }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}">
@endpush

@section('content')
@php
    $doneStatuses = ['done', 'closed', 'completed', 'resolved'];
    $movableSprints = $project->sprints->whereIn('status', ['planned', 'active']);
@endphp
<div class="project-dashboard-vue sprint-project-page container-fluid py-3" style="max-width: 1400px;">
    <div class="pd-header mb-3">
        <div class="pd-header-left">
            <div class="pd-breadcrumb mb-1">
                <a href="{{ route('projects.index') }}">Dự án</a><span>/</span><span>{{ $project->name }}</span><span>/</span><span class="text-dark font-weight-bold">Backlog / Sprint</span>
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
        <a href="{{ route('sprints.index', $project) }}" class="pd-nav-item active">Backlog / Sprint</a>
        <a href="{{ route('projects.timeline', $project) }}" class="pd-nav-item">Timeline</a>
        <a href="{{ route('projects.roadmap', $project) }}" class="pd-nav-item">Roadmap</a>
        <a href="{{ route('projects.reports.show', $project) }}" class="pd-nav-item">Báo cáo</a>
    </div>

    <section class="sprint-jira-toolbar" aria-label="Tổng quan Backlog và Sprint">
        <div class="sprint-jira-toolbar-title"><span class="sprint-jira-toolbar-icon"><i class="fa fa-list-alt"></i></span><div><h2>Backlog &amp; Sprint</h2><p>Lập kế hoạch, ưu tiên và theo dõi công việc theo từng Sprint.</p></div></div>
        <div class="sprint-jira-toolbar-stats"><span><strong>{{ $backlog->count() }}</strong> Backlog</span><span><strong>{{ $project->sprints->where('status', 'active')->count() }}</strong> Đang chạy</span></div>
    </section>

    <div class="jira-workspace sprint-workspace sprint-jira-inspired">
    <div class="jira-workspace-heading" aria-hidden="true" style="display: none;">
        <div><span class="jira-eyebrow">{{ $project->project_code }}</span><h1>{{ $project->name }} / Backlog & Sprint</h1><p>Lập kế hoạch Sprint, ưu tiên Backlog và theo dõi tiến độ công việc trong một nơi.</p></div>
        <div class="jira-view-links"><a href="{{ route('projects.board', $project) }}">Board</a><a class="active" href="{{ route('sprints.index', $project) }}">Backlog / Sprint</a><a href="{{ route('projects.roadmap', $project) }}">Roadmap</a><a href="{{ route('projects.reports.show', $project) }}">Báo cáo</a></div>
    </div>
    <div class="sprint-summary-grid">
        <div class="sprint-summary-card"><span>Backlog</span><strong>{{ $backlog->count() }}</strong><small>công việc chưa lên Sprint</small></div>
        <div class="sprint-summary-card"><span>Sprint đang chạy</span><strong>{{ $project->sprints->where('status', 'active')->count() }}</strong><small>chỉ một Sprint được kích hoạt</small></div>
        <div class="sprint-summary-card"><span>Tổng Sprint</span><strong>{{ $project->sprints->count() }}</strong><small>đã lên kế hoạch và đã hoàn thành</small></div>
    </div>
    <div class="jira-sprint-layout">
        <section class="jira-sprint-main">
            <div class="jira-panel-heading"><div><h2>Backlog <span>{{ $backlog->count() }}</span></h2><small>Chọn Sprint để đưa công việc vào kế hoạch, hoặc giữ lại trong Backlog.</small></div><a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-sm">+ Tạo issue</a></div>
            <div class="sprint-list-columns" aria-hidden="true"><span>Công việc</span><span>Sprint đích</span></div>
            <div class="jira-backlog-list sprint-task-list">
                @forelse($backlog as $task)
                    <article class="jira-backlog-item sprint-task-row"><a class="sprint-task-row__main" href="{{ route('tasks.show', $task) }}"><span class="jira-issue-key">{{ $task->issue_key ?? 'TP-' . str_pad($task->id, 5, '0', STR_PAD_LEFT) }}</span><span class="jira-issue-type jira-type-{{ $task->issue_type }}">{{ strtoupper($task->issue_type ?? 'task') }}</span><strong>{{ $task->name }}</strong><small class="sprint-task-row__meta"><span><i class="fa fa-user-o"></i> {{ $task->assignee?->name ?? 'Chưa phân công' }}</span><span class="jira-status jira-status-{{ strtolower($task->status ?? 'todo') }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span></small></a>
                    @if($movableSprints->isNotEmpty())<form method="POST" action="{{ route('sprints.tasks.move', [$project, $task]) }}" class="sprint-task-row__move">@csrf @method('PUT')<select name="sprint_id" aria-label="Chuyển task vào Sprint" onchange="this.form.submit()"><option value="">Đưa vào Sprint…</option>@foreach($movableSprints as $sprint)<option value="{{ $sprint->id }}">{{ $sprint->name }} ({{ $sprint->status === 'active' ? 'Đang chạy' : 'Kế hoạch' }})</option>@endforeach</select></form>@endif</article>
                @empty <div class="jira-empty-column">Backlog đang trống. Tạo issue mới hoặc trả task từ một Sprint về đây.</div> @endforelse
            </div>
        </section>
        <aside class="jira-sprint-side"><div class="jira-panel-heading"><h2>Tạo Sprint</h2></div><form method="POST" action="{{ route('sprints.store', $project) }}" class="jira-sprint-form">@csrf<input name="name" class="form-control" placeholder="Tên Sprint" required><input name="goal" class="form-control" placeholder="Mục tiêu Sprint"><div class="jira-sprint-dates"><input type="date" name="starts_at" class="form-control"><input type="date" name="ends_at" class="form-control"></div><button class="btn btn-primary btn-sm">Tạo Sprint</button></form></aside>
    </div>
    <section class="sprint-board-section"><div class="jira-panel-heading"><div><h2>Sprint</h2><small>Tiến độ được tính từ công việc đã hoàn thành trong từng Sprint.</small></div></div><div class="sprint-board-grid">
        @forelse($project->sprints as $sprint)
            @php $total = $sprint->tasks->count(); $done = $sprint->tasks->filter(fn ($task) => in_array(strtolower($task->status), $doneStatuses, true))->count(); $progress = $total ? (int) round(($done / $total) * 100) : 0; @endphp
            <article class="jira-sprint-card sprint-board-card"><header><div><strong>{{ $sprint->name }}</strong><span class="jira-status jira-status-{{ $sprint->status }}">{{ $sprint->status === 'active' ? 'Đang chạy' : ($sprint->status === 'planned' ? 'Kế hoạch' : 'Đã đóng') }}</span></div><small>{{ $sprint->starts_at?->format('d/m') ?? 'Chưa đặt ngày' }} – {{ $sprint->ends_at?->format('d/m/Y') ?? 'Chưa đặt ngày' }}</small></header><p class="sprint-board-card__goal">{{ $sprint->goal ?: 'Chưa có mục tiêu Sprint.' }}</p><div class="sprint-progress-label"><span>{{ $done }}/{{ $total }} đã hoàn thành</span><strong>{{ $progress }}%</strong></div><div class="sprint-progress" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $progress }}%"></span></div><div class="jira-sprint-actions">@if($sprint->status === 'planned')<form method="POST" action="{{ route('sprints.start', [$project, $sprint]) }}">@csrf<button class="btn btn-outline-success btn-xs">Bắt đầu</button></form>@endif @if($sprint->status === 'active')<form method="POST" action="{{ route('sprints.close', [$project, $sprint]) }}">@csrf<button class="btn btn-outline-secondary btn-xs">Đóng Sprint</button></form>@endif</div><div class="sprint-board-card__tasks">
                @forelse($sprint->tasks as $task)<div class="sprint-task-row"><a class="sprint-task-row__main" href="{{ route('tasks.show', $task) }}"><span class="jira-issue-key">{{ $task->issue_key ?? 'TP-' . str_pad($task->id, 5, '0', STR_PAD_LEFT) }}</span><strong>{{ $task->name }}</strong><small class="sprint-task-row__meta"><span><i class="fa fa-user-o"></i> {{ $task->assignee?->name ?? 'Chưa phân công' }}</span><span class="jira-status jira-status-{{ strtolower($task->status ?? 'todo') }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span></small></a><form method="POST" action="{{ route('sprints.tasks.move', [$project, $task]) }}" class="sprint-task-row__move">@csrf @method('PUT')<select name="sprint_id" aria-label="Chuyển task"><option value="{{ $sprint->id }}">{{ $sprint->name }}</option><option value="">Trả về Backlog</option>@foreach($movableSprints->where('id', '!=', $sprint->id) as $other)<option value="{{ $other->id }}">{{ $other->name }}</option>@endforeach</select><button class="btn btn-link btn-xs">Chuyển</button></form></div>@empty <p class="sprint-empty">Chưa có công việc. Hãy đưa task từ Backlog vào Sprint này.</p> @endforelse
            </div></article>
        @empty <div class="jira-empty-column">Chưa có Sprint. Tạo Sprint đầu tiên để bắt đầu kế hoạch.</div> @endforelse
    </div></section>
</div>
</div>
@endsection
