@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('build/assets/edit-shared.css') }}?v={{ file_exists(public_path('build/assets/edit-shared.css')) ? filemtime(public_path('build/assets/edit-shared.css')) : time() }}" rel="stylesheet">

<div class="col-md-12 p-0">
    <div class="py-3 w-100">
        <!-- Header Banner -->
        <div class="tp-header-banner mb-4">
            <div class="tp-header-content">
                <div class="tp-eyebrow">
                    <i class="fa fa-sitemap"></i>
                    <span>TaskPilot Workflow Engine</span>
                </div>
                <h1 class="m-0" style="font-size: 24px; font-weight: 700; color: #0f172a;">
                    Quản lý Quy trình &amp; Trạng thái
                </h1>
                <p class="m-0 text-muted small mt-1">
                    Cấu hình vòng đời công việc, trạng thái linh hoạt, ma trận quy tắc chuyển trạng thái (Transition Rules) và cổng kiểm soát cho từng dự án.
                </p>
            </div>
            <div class="tp-header-actions">
                <a href="{{ route('tasks.index') }}" class="tp-btn tp-btn-secondary">
                    <i class="fa fa-tasks" style="color: #ffffff !important;"></i>
                    <span>Xem bảng công việc</span>
                </a>
                <a href="{{ route('projects.index') }}" class="tp-btn tp-btn-light">
                    <i class="fa fa-folder-o"></i>
                    <span>Danh sách dự án</span>
                </a>
            </div>
        </div>

        <!-- Feature Highlight Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="edit-card h-100 p-3" style="border-left: 4px solid #3b82f6;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                            <i class="fa fa-list-ol"></i>
                        </div>
                        <h4 class="m-0 font-weight-bold" style="font-size: 14px; color: #1e293b;">Trạng thái Linh hoạt</h4>
                    </div>
                    <p class="m-0 text-muted" style="font-size: 12.5px; line-height: 1.5;">
                        Không giới hạn ở các trạng thái cố định. Dễ dàng thêm mới, sửa tên, chọn màu sắc, icon, sắp xếp thứ tự và bật/tắt kích hoạt trạng thái trực tiếp trong từng dự án.
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="edit-card h-100 p-3" style="border-left: 4px solid #8b5cf6;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                            <i class="fa fa-random"></i>
                        </div>
                        <h4 class="m-0 font-weight-bold" style="font-size: 14px; color: #1e293b;">Quy tắc Chuyển trạng thái (State Transition)</h4>
                    </div>
                    <p class="m-0 text-muted" style="font-size: 12.5px; line-height: 1.5;">
                        Thiết lập ma trận chuyển đổi: chỉ định rõ ràng từ trạng thái A được phép chuyển sang trạng thái nào (ví dụ: To Do &rarr; In Progress &rarr; Done).
                    </p>
                </div>
            </div>
        </div>

        <!-- Projects Workflow Configuration List -->
        <div class="edit-card">
            <div class="edit-card-header d-flex justify-content-between align-items-center">
                <h2 class="edit-card-title">
                    <i class="fa fa-folder text-primary"></i>
                    Quy trình theo từng dự án
                </h2>
                <span class="text-muted small">Tổng số: <strong>{{ count($projects) }}</strong> dự án</span>
            </div>

            <div class="edit-card-body p-0">
                <div class="table-responsive">
                    <table class="tp-table m-0">
                        <thead>
                            <tr>
                                <th style="width: 260px;">Dự án</th>
                                <th style="width: 140px;">Loại quy trình</th>
                                <th>Tiến trình trạng thái (Workflow Pipeline)</th>
                                <th style="width: 180px; text-align: right; white-space: nowrap;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                @php
                                    $wf = $project->effective_workflow;
                                    $rawStatuses = $wf['statuses'] ?? [];
                                    $activeStatuses = collect($rawStatuses)->filter(fn($s) => ($s['is_active'] ?? true) !== false)->values();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700;">
                                                📁
                                            </div>
                                            <div>
                                                <a href="{{ route('projects.show', $project->id) }}" style="font-weight: 600; color: #0f172a; text-decoration: none;">
                                                    {{ $project->name }}
                                                </a>
                                                <div class="text-muted" style="font-size: 11px;">
                                                    Mã: <strong>{{ $project->project_code ?: 'PRJ-'.$project->id }}</strong> &middot; {{ $project->tasks_count ?? $project->tasks->count() }} công việc
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 600; padding: 4px 8px; border-radius: 6px;">
                                            {{ strtoupper($project->project_type ?? 'Software') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            @foreach($activeStatuses as $st)
                                                <span
                                                    class="tp-badge"
                                                    style="background-color: {{ $st['color'] ?? '#3b82f6' }}18; color: {{ $st['color'] ?? '#3b82f6' }}; border: 1px solid {{ $st['color'] ?? '#3b82f6' }}40; font-size: 11px; padding: 2px 7px;"
                                                >
                                                    <i class="fa {{ $st['icon'] ? (str_starts_with($st['icon'], 'fa') ? $st['icon'] : 'fa '.$st['icon']) : 'fa-circle-o' }}" style="font-size: 10px;"></i>
                                                    {{ $st['name'] }}
                                                </span>
                                                @if(!$loop->last)
                                                    <i class="fa fa-angle-right text-muted" style="font-size: 10px;"></i>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <a href="{{ route('projects.show', $project->id) }}#workflow" class="tp-btn tp-btn-primary btn-sm" style="padding: 6px 14px; font-size: 12px; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; gap: 6px;" title="Cấu hình quy trình">
                                            <i class="fa fa-sitemap" style="color: #ffffff !important;"></i>
                                            <span style="white-space: nowrap;">Quản lý quy trình</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fa fa-inbox font-size-24 mb-2 d-block"></i>
                                        Bạn chưa tham gia dự án nào. Hãy tạo dự án mới để cấu hình quy trình.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
