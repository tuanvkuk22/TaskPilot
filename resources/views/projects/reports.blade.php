@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/project-dashboard.css') }}?v={{ file_exists(public_path('build/assets/project-dashboard.css')) ? filemtime(public_path('build/assets/project-dashboard.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<div class="project-report-page container-fluid py-3">
    <div class="pd-header mb-3">
        <div class="pd-header-left">
            <div class="pd-breadcrumb mb-1"><a href="{{ route('projects.index') }}">Dự án</a><span>/</span><span>{{ $project->name }}</span><span>/</span><span class="text-dark font-weight-bold">Báo cáo</span></div>
            <div class="pd-title-row"><h1 class="pd-title m-0">{{ $project->name }}</h1><span class="pd-project-key">{{ $project->project_code ?? 'PROJ' }}</span><span class="pd-project-status">{{ strtoupper($project->status ?? 'active') }}</span><a href="{{ route('projects.edit', $project) }}" class="pd-settings-btn" title="Cài đặt dự án"><i class="fa fa-cog"></i></a></div>
            <p class="project-report-subtitle">Báo cáo tiến độ, Sprint và thời gian làm việc của dự án.</p>
        </div>
        <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="tp-btn tp-btn-primary"><i class="fa fa-plus-circle" style="color:#fff!important"></i><span>Tạo công việc mới</span></a>
    </div>

    <nav class="pd-project-nav mb-4" aria-label="Điều hướng dự án">
        <a href="{{ route('projects.index') }}" class="pd-nav-item">Dự án</a><a href="{{ route('projects.show', $project) }}" class="pd-nav-item">Tổng quan</a><a href="{{ route('projects.board', $project) }}" class="pd-nav-item">Bảng</a><a href="{{ route('projects.members.index', $project) }}" class="pd-nav-item">Thành viên</a><a href="{{ route('projects.roles.index', $project) }}" class="pd-nav-item">Vai trò &amp; Quyền</a><a href="{{ route('sprints.index', $project) }}" class="pd-nav-item">Backlog / Sprint</a><a href="{{ route('projects.timeline', $project) }}" class="pd-nav-item">Timeline</a><a href="{{ route('projects.roadmap', $project) }}" class="pd-nav-item">Lịch biểu</a><a href="{{ route('projects.reports.show', $project) }}" class="pd-nav-item active">Báo cáo</a>
    </nav>

    <section class="project-report-hero">
        <div><span class="project-report-eyebrow"><i class="fa fa-bar-chart"></i> BÁO CÁO DỰ ÁN</span><h2>Tổng quan tiến độ</h2><p>Theo dõi khối lượng công việc và kết quả của các Sprint trong một nơi.</p></div>
        <div class="project-report-progress"><strong>{{ $summary['progress'] }}%</strong><span>Hoàn thành toàn dự án</span><div class="project-report-progress-track"><span style="width:{{ $summary['progress'] }}%"></span></div></div>
    </section>

    <section class="project-report-stats" aria-label="Chỉ số dự án">
        <article class="project-report-stat stat-blue"><span class="project-report-stat-icon"><i class="fa fa-list-alt"></i></span><div><small>Tổng công việc</small><strong>{{ $summary['total'] }}</strong><span>Trong toàn dự án</span></div></article>
        <article class="project-report-stat stat-green"><span class="project-report-stat-icon"><i class="fa fa-check-circle"></i></span><div><small>Đã hoàn thành</small><strong>{{ $summary['done'] }}</strong><span>{{ $summary['progress'] }}% khối lượng</span></div></article>
        <article class="project-report-stat stat-purple"><span class="project-report-stat-icon"><i class="fa fa-line-chart"></i></span><div><small>Tiến độ</small><strong>{{ $summary['progress'] }}%</strong><span>Theo trạng thái task</span></div></article>
        <article class="project-report-stat stat-amber"><span class="project-report-stat-icon"><i class="fa fa-clock-o"></i></span><div><small>Worklog</small><strong>{{ number_format($summary['hours'], 1) }}h</strong><span>Thời gian đã ghi nhận</span></div></article>
    </section>

    <section class="project-report-panel">
        <div class="project-report-panel-head"><div><h2><i class="fa fa-filter"></i> Báo cáo theo Sprint</h2><p>Chọn Sprint để lọc dữ liệu hoặc xuất báo cáo theo phạm vi hiện tại.</p></div><div class="project-report-exports"><a class="tp-btn project-report-export-excel" href="{{ route('projects.reports.excel', $project) }}?sprint_id={{ $selectedSprint }}"><i class="fa fa-file-excel-o"></i> Xuất Excel</a><a class="tp-btn project-report-export-pdf" target="_blank" rel="noopener" href="{{ route('projects.reports.pdf', $project) }}?sprint_id={{ $selectedSprint }}"><i class="fa fa-file-pdf-o"></i> Xuất PDF</a></div></div>
        <form method="GET" class="project-report-filter"><label for="report-sprint">Sprint</label><select id="report-sprint" name="sprint_id" onchange="this.form.submit()"><option value="">Tất cả Sprint</option>@foreach($project->sprints as $sprint)<option value="{{ $sprint->id }}" @selected($selectedSprint === $sprint->id)>{{ $sprint->name }}</option>@endforeach</select>@if($selectedSprint)<a href="{{ route('projects.reports.show', $project) }}" class="project-report-reset"><i class="fa fa-refresh"></i> Xóa bộ lọc</a>@endif</form>
        <div class="project-report-table-wrap"><table class="project-report-table"><thead><tr><th>Sprint</th><th>Trạng thái</th><th>Công việc</th><th>Tiến độ</th><th>Worklog</th></tr></thead><tbody>@forelse($sprintRows as $row)<tr><td><strong>{{ $row['name'] }}</strong></td><td><span class="project-report-status">{{ ucfirst($row['status']) }}</span></td><td>{{ $row['done'] }} / {{ $row['total'] }} hoàn thành</td><td><div class="project-report-table-progress"><span style="width:{{ $row['progress'] }}%"></span></div><strong>{{ $row['progress'] }}%</strong></td><td>{{ number_format($row['hours'], 1) }}h</td></tr>@empty<tr><td colspan="5" class="project-report-empty"><i class="fa fa-folder-open-o"></i> Chưa có Sprint phù hợp.</td></tr>@endforelse</tbody></table></div>
    </section>
</div>
@endsection
