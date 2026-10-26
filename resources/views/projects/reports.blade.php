@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/project-dashboard-luxury.css') }}?v={{ file_exists(public_path('css/project-dashboard-luxury.css')) ? filemtime(public_path('css/project-dashboard-luxury.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('css/project-reports.css') }}?v={{ file_exists(public_path('css/project-reports.css')) ? filemtime(public_path('css/project-reports.css')) : time() }}" rel="stylesheet">
@endpush

@section('content')
<div class="col-xs-12 col-md-12 p-0">
    <div class="project-report-page">
        <!-- Project Header -->
        <div class="pd-header mb-3" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div class="pd-header-left">
                <div class="pd-breadcrumb" style="margin-bottom: 6px; font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    <a href="{{ route('projects.index') }}" style="color: #64748b; text-decoration: none;">Dự án</a>
                    <span>/</span>
                    <a href="{{ route('projects.show', $project) }}" style="color: #64748b; text-decoration: none;">{{ $project->name }}</a>
                    <span>/</span>
                    <span style="color: #0f172a; font-weight: 700;">Báo cáo</span>
                </div>
                <div class="pd-title-row" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h1 class="pd-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a;">{{ $project->name }}</h1>
                    <span class="pd-project-key">{{ $project->project_code ?? 'PROJ' }}</span>
                    <span class="pd-project-status">{{ strtoupper($project->status ?? 'active') }}</span>
                    <a href="{{ route('projects.edit', $project) }}" class="pd-settings-btn" title="Cài đặt dự án">
                        <i class="fa fa-cog"></i>
                    </a>
                </div>
                <p class="project-report-subtitle" style="margin: 6px 0 0 0; color: #64748b; font-size: 13px;">
                    Báo cáo tiến độ, khối lượng công việc, Sprint và thời gian làm việc của dự án.
                </p>
            </div>
            <div>
                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="tp-btn tp-btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; text-decoration: none;">
                    <i class="fa fa-plus-circle" style="color: #ffffff !important;"></i>
                    <span>Tạo công việc mới</span>
                </a>
            </div>
        </div>

        <!-- Unified Project Sub-Nav Bar -->
        @include('projects.partials.nav', ['project' => $project, 'active' => 'reports'])

        <!-- Hero Progress Banner -->
        <section class="project-report-hero">
            <div>
                <span class="project-report-eyebrow">
                    <i class="fa fa-bar-chart"></i> Báo cáo tổng thể dự án
                </span>
                <h2>Tổng quan tiến độ thực hiện</h2>
                <p>Theo dõi khối lượng công việc, tỷ lệ hoàn thành và kết quả của các Sprint trong dự án.</p>
            </div>
            <div class="project-report-progress">
                <strong>{{ $summary['progress'] }}%</strong>
                <span>Hoàn thành toàn dự án</span>
                <div class="project-report-progress-track">
                    <span style="width: {{ $summary['progress'] }}%;"></span>
                </div>
            </div>
        </section>

        <!-- KPI Stats Grid -->
        <section class="project-report-stats" aria-label="Chỉ số dự án">
            <article class="project-report-stat stat-blue">
                <span class="project-report-stat-icon">
                    <i class="fa fa-list-alt"></i>
                </span>
                <div>
                    <small>Tổng công việc</small>
                    <strong>{{ $summary['total'] }}</strong>
                    <span>Trong toàn dự án</span>
                </div>
            </article>

            <article class="project-report-stat stat-green">
                <span class="project-report-stat-icon">
                    <i class="fa fa-check-circle"></i>
                </span>
                <div>
                    <small>Đã hoàn thành</small>
                    <strong>{{ $summary['done'] }}</strong>
                    <span>{{ $summary['progress'] }}% khối lượng</span>
                </div>
            </article>

            <article class="project-report-stat stat-purple">
                <span class="project-report-stat-icon">
                    <i class="fa fa-line-chart"></i>
                </span>
                <div>
                    <small>Tiến độ</small>
                    <strong>{{ $summary['progress'] }}%</strong>
                    <span>Theo trạng thái task</span>
                </div>
            </article>

            <article class="project-report-stat stat-amber">
                <span class="project-report-stat-icon">
                    <i class="fa fa-clock-o"></i>
                </span>
                <div>
                    <small>Worklog</small>
                    <strong>{{ number_format($summary['hours'], 1) }}h</strong>
                    <span>Thời gian đã ghi nhận</span>
                </div>
            </article>
        </section>

        <!-- Sprint Report Panel -->
        <section class="project-report-panel">
            <div class="project-report-panel-head">
                <div>
                    <h2><i class="fa fa-filter text-primary"></i> Báo cáo chi tiết theo Sprint</h2>
                    <p>Chọn Sprint để lọc dữ liệu hoặc xuất báo cáo chi tiết theo phạm vi hiện tại.</p>
                </div>
                <div class="project-report-exports">
                    <a class="project-report-export-excel" href="{{ route('projects.reports.excel', $project) }}?sprint_id={{ $selectedSprint }}">
                        <i class="fa fa-file-excel-o"></i> Xuất Excel
                    </a>
                    <a class="project-report-export-pdf" target="_blank" rel="noopener" href="{{ route('projects.reports.pdf', $project) }}?sprint_id={{ $selectedSprint }}">
                        <i class="fa fa-file-pdf-o"></i> Xuất PDF
                    </a>
                </div>
            </div>

            <!-- Sprint Filter Toolbar -->
            <form method="GET" class="project-report-filter">
                <label for="report-sprint"><i class="fa fa-cubes me-1"></i> Lọc Sprint:</label>
                <select id="report-sprint" name="sprint_id" onchange="this.form.submit()">
                    <option value="">Tất cả Sprint</option>
                    @foreach($project->sprints as $sprint)
                        <option value="{{ $sprint->id }}" @selected($selectedSprint === $sprint->id)>
                            {{ $sprint->name }} ({{ ucfirst($sprint->status) }})
                        </option>
                    @endforeach
                </select>
                @if($selectedSprint)
                    <a href="{{ route('projects.reports.show', $project) }}" class="project-report-reset">
                        <i class="fa fa-times"></i> Xóa bộ lọc
                    </a>
                @endif
            </form>

            <!-- Table of Sprints -->
            <div class="project-report-table-wrap">
                <table class="project-report-table">
                    <thead>
                        <tr>
                            <th>Sprint</th>
                            <th>Trạng thái</th>
                            <th>Công việc</th>
                            <th>Tiến độ</th>
                            <th>Worklog</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sprintRows as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row['name'] }}</strong>
                                </td>
                                <td>
                                    <span class="project-report-status" style="{{ $row['status'] === 'closed' ? 'background: #ecfdf5; color: #059669;' : ($row['status'] === 'active' ? 'background: #eff6ff; color: #2563eb;' : 'background: #f1f5f9; color: #64748b;') }}">
                                        {{ ucfirst($row['status']) }}
                                    </span>
                                </td>
                                <td>{{ $row['done'] }} / {{ $row['total'] }} hoàn thành</td>
                                <td>
                                    <div class="project-report-table-progress">
                                        <span style="width: {{ $row['progress'] }}%;"></span>
                                    </div>
                                    <strong style="color: #0f172a;">{{ $row['progress'] }}%</strong>
                                </td>
                                <td>
                                    <i class="fa fa-clock-o text-muted me-1"></i>
                                    <strong>{{ number_format($row['hours'], 1) }}h</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="project-report-empty">
                                    <i class="fa fa-folder-open-o" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                                    <span>Chưa có Sprint nào phù hợp trong dự án này.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
