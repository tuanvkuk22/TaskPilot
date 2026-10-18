@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/edit-shared.css') }}?v={{ file_exists(public_path('build/assets/edit-shared.css')) ? filemtime(public_path('build/assets/edit-shared.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<!-- Local Open-Source CKEditor 4.22.1 (No License Key Required) -->
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>

@php
    $canEdit = auth()->check();
    $projectWorkflow = $task->project ? $task->project->effective_workflow : \ProjectsManager\Project::defaultWorkflowForType('software');
    $rawStatuses = $active_statuses ?? $projectWorkflow['statuses'] ?? [
        ['key' => 'todo', 'name' => 'Cần làm', 'color' => '#64748b', 'icon' => 'fa-circle-o', 'is_active' => true],
        ['key' => 'in_progress', 'name' => 'Đang thực hiện', 'color' => '#3b82f6', 'icon' => 'fa-spinner', 'is_active' => true],
        ['key' => 'done', 'name' => 'Hoàn thành', 'color' => '#10b981', 'icon' => 'fa-check-circle', 'is_active' => true],
    ];
    $wfStatuses = collect($rawStatuses)->filter(fn($s) => ($s['is_active'] ?? true) !== false)->values();
    $currStatus = $wfStatuses->firstWhere('key', $task->status) ?? [
        'key' => $task->status,
        'name' => ucfirst($task->status ?? 'todo'),
        'color' => '#64748b',
        'icon' => 'fa-circle-o'
    ];

    $configuredTransitions = $projectWorkflow['transitions'][$task->status] ?? null;
    if (is_array($configuredTransitions)) {
        $allowedTargetKeys = $configuredTransitions;
    } else {
        $allowedTargetKeys = $wfStatuses->pluck('key')->filter(fn($k) => $k !== $task->status)->toArray();
    }
    $nextAllowedStatuses = $wfStatuses->filter(fn($s) => in_array($s['key'], $allowedTargetKeys, true) && $s['key'] !== $task->status)->values();

    $typeIcons = [
        'task' => 'fa-check-square-o',
        'bug' => 'fa-bug',
        'story' => 'fa-bookmark',
        'epic' => 'fa-bolt',
        'subtask' => 'fa-code-fork',
    ];
    $typeLabels = [
        'task' => 'Task',
        'bug' => 'Bug',
        'story' => 'Story',
        'epic' => 'Epic',
        'subtask' => 'Sub-task',
    ];

    $priorityMap = [
        'low' => ['label' => 'Thấp', 'class' => 'tp-badge-priority-low', 'icon' => 'fa-arrow-down', 'color' => '#10b981'],
        'medium' => ['label' => 'Trung bình', 'class' => 'tp-badge-priority-medium', 'icon' => 'fa-minus', 'color' => '#f59e0b'],
        'high' => ['label' => 'Cao / Gấp', 'class' => 'tp-badge-priority-high', 'icon' => 'fa-arrow-up', 'color' => '#ef4444'],
    ];
    $pInfo = $priorityMap[$task->priority] ?? $priorityMap['medium'];
@endphp

<div class="col-md-12 p-0">
    <div class="task-show-wrapper py-3 w-100">
        <!-- CSRF Token Meta -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Toast Notification Container -->
        <div id="tp-task-toast" class="tp-task-toast" style="display: none;" role="status">
            <i class="fa fa-check-circle"></i>
            <span id="tp-toast-message">Đã lưu thay đổi.</span>
        </div>

        <!-- Breadcrumb Navigation -->
        <div class="tp-breadcrumb-bar mb-3">
            <a href="{{ route('tasks.index') }}" class="breadcrumb-link">
                <i class="fa fa-tasks me-1"></i>Công việc
            </a>
            <span class="breadcrumb-sep">/</span>
            @if($task->project)
                <a href="{{ route('projects.show', $task->project_id) }}" class="breadcrumb-link">
                    📁 {{ $task->project->name }}
                </a>
                <span class="breadcrumb-sep">/</span>
            @endif
            <span class="breadcrumb-current">{{ $task->project->project_code ?? 'TASK' }}-{{ $task->id }}</span>
        </div>

        <!-- Header Banner -->
        <div class="tp-header-banner">
            <div class="tp-header-content">
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span class="tp-badge tp-badge-type-{{ $task->issue_type ?? 'task' }}">
                        <i class="fa {{ $typeIcons[$task->issue_type] ?? 'fa-check-square-o' }}"></i>
                        <span>{{ $typeLabels[$task->issue_type] ?? 'Task' }}</span>
                    </span>
                    <span class="task-key-tag font-weight-bold" style="font-size: 13px;">
                        {{ $task->project->project_code ?? 'TASK' }}-{{ $task->id }}
                    </span>
                    <span class="tp-badge {{ $pInfo['class'] }}">
                        <i class="fa {{ $pInfo['icon'] }}"></i>
                        <span>{{ $pInfo['label'] }}</span>
                    </span>
                </div>

                <!-- Jira-style Inline Editable Title -->
                <div class="jira-title-container mt-1 mb-1">
                    <div id="jira-title-view" class="jira-editable-field {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('title')" @endif title="{{ $canEdit ? 'Bấm để sửa tên công việc' : '' }}">
                        <h1 id="task-name-text" class="m-0 d-inline" style="font-size: 22px; font-weight: 700; color: #0f172a; line-height: 1.3;">
                            {{ $task->name }}
                        </h1>
                        @if($canEdit)
                            <i class="fa fa-pencil jira-hover-pencil ms-2 text-muted" style="font-size: 14px;"></i>
                        @endif
                    </div>

                    @if($canEdit)
                    <div id="jira-title-edit" class="jira-inline-form" style="display: none;">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <input type="text" id="input-task-name" class="edit-input font-size-18 font-weight-bold" value="{{ $task->name }}" style="max-width: 600px;">
                            <button type="button" class="tp-btn tp-btn-primary btn-sm" onclick="saveInlineField('name', document.getElementById('input-task-name').value)">
                                <i class="fa fa-check text-white"></i> Lưu
                            </button>
                            <button type="button" class="tp-btn tp-btn-light btn-sm" onclick="cancelInlineEdit('title')">
                                <i class="fa fa-times"></i> Hủy
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <p class="m-0 text-muted small">Chi tiết tiến độ, quy trình workflow, phân công và trao đổi</p>
            </div>

            <div class="tp-header-actions">
                <a href="{{ route('tasks.index') }}" class="tp-btn tp-btn-secondary">
                    <i class="fa fa-arrow-left" style="color: #ffffff !important;"></i>
                    <span>Danh sách</span>
                </a>
                <a href="{{ route('tasks.edit', $task->id) }}" class="tp-btn tp-btn-light" title="Chỉnh sửa toàn bộ thông tin">
                    <i class="fa fa-external-link"></i>
                    <span>Sửa đầy đủ</span>
                </a>
                <form method="POST" action="{{ route('tasks.destroy', $task->id) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa công việc này?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="tp-btn tp-btn-danger">
                        <i class="fa fa-trash" style="color: #ffffff !important;"></i>
                        <span>Xóa</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Modern Configurable Workflow Stepper Card -->
        <div class="tp-workflow-card mb-4">
            <div class="tp-workflow-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-sitemap" style="color: #6366f1;"></i>
                    <span class="font-weight-bold" style="color: #1e293b; font-size: 13.5px;">
                        Quy trình dự án ({{ strtoupper($task->project->project_type ?? 'Software') }}):
                    </span>
                    <span class="tp-badge" style="background-color: {{ $currStatus['color'] ?? '#3b82f6' }}20; color: {{ $currStatus['color'] ?? '#3b82f6' }}; border: 1px solid {{ $currStatus['color'] ?? '#3b82f6' }}; font-weight: 700;">
                        <i class="fa {{ $currStatus['icon'] ?? 'fa-circle-o' }}"></i>
                        {{ $currStatus['name'] }}
                    </span>
                </div>

                <!-- Next Status Quick Action Transition Controls -->
                @if($canEdit && $nextAllowedStatuses->isNotEmpty())
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small font-weight-bold">Chuyển trạng thái:</span>
                        @foreach($nextAllowedStatuses as $nextSt)
                            <button
                                type="button"
                                class="tp-status-step-btn"
                                style="border-color: {{ $nextSt['color'] ?? '#3b82f6' }}; color: {{ $nextSt['color'] ?? '#3b82f6' }};"
                                onclick="changeTaskStatus('{{ $nextSt['key'] }}')"
                                title="Chuyển công việc sang trạng thái {{ $nextSt['name'] }}"
                            >
                                <i class="fa {{ $nextSt['icon'] ?? 'fa-arrow-right' }}"></i>
                                <span>{{ $nextSt['name'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Visual Workflow Progression Stepper -->
            <div class="tp-workflow-stepper">
                @foreach($wfStatuses as $idx => $st)
                    @php
                        $isActive = $st['key'] === $task->status;
                        $isAllowedNext = in_array($st['key'], $allowedTargetKeys, true) && !$isActive;
                    @endphp
                    <div class="tp-step-node {{ $isActive ? 'is-active' : '' }} {{ $isAllowedNext ? 'is-allowed-next' : '' }}">
                        <div class="step-indicator" style="{{ $isActive ? 'background-color: ' . ($st['color'] ?? '#3b82f6') . '; border-color: ' . ($st['color'] ?? '#3b82f6') . '; color: #fff;' : 'border-color: ' . ($st['color'] ?? '#cbd5e1') . ';' }}">
                            @if($isActive)
                                <i class="fa {{ $st['icon'] ?? 'fa-check' }}"></i>
                            @else
                                <span class="step-number">{{ $idx + 1 }}</span>
                            @endif
                        </div>
                        <div class="step-label-wrap">
                            <span class="step-title" style="{{ $isActive ? 'color: ' . ($st['color'] ?? '#1e293b') . '; font-weight: 700;' : '' }}">
                                {{ $st['name'] }}
                            </span>
                            @if($isActive)
                                <span class="step-badge-current">Hiện tại</span>
                            @elseif($isAllowedNext)
                                <button type="button" class="step-action-link" onclick="changeTaskStatus('{{ $st['key'] }}')">Chuyển ngay</button>
                            @endif
                        </div>
                    </div>
                    @if(!$loop->last)
                        <div class="tp-step-connector {{ $isActive ? 'connector-active' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- 2-Column Grid Layout (70 / 30) -->
        <div class="edit-grid">
            <!-- Main Content (~70%) -->
            <div class="edit-col">
                <!-- 1. Task Description Card (Unified Local CKEditor 4.22.1) -->
                <div class="edit-card mb-4">
                    <div class="edit-card-header d-flex justify-content-between align-items-center">
                        <h2 class="edit-card-title">
                            <i class="fa fa-align-left text-primary"></i>
                            Mô tả công việc
                        </h2>
                        @if($canEdit)
                            <button type="button" class="tp-btn tp-btn-primary btn-sm" onclick="saveDescriptionCKEditor()">
                                <i class="fa fa-save" style="color: #ffffff !important;"></i>
                                <span>Lưu mô tả</span>
                            </button>
                        @endif
                    </div>

                    <div class="edit-card-body">
                        @if($canEdit)
                            <div class="w-100">
                                <textarea id="task-description-editor" name="description" class="edit-textarea w-100" rows="6" placeholder="Nhập mô tả chi tiết, tiêu chí hoàn thành, checklist...">{{ $task->description }}</textarea>
                                <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <span class="text-muted small">
                                        <i class="fa fa-info-circle me-1"></i>Hỗ trợ định dạng rich text, danh sách, bảng biểu và chèn ảnh minh họa.
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="tp-description-text ck-content-body w-100">
                                {!! $task->description ? $task->description : '<span class="text-muted">Chưa có mô tả chi tiết cho công việc này.</span>' !!}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 2. Activity Section: Comments & Worklogs -->
                <div class="edit-card">
                    <div class="edit-card-header">
                        <ul class="nav nav-tabs tp-activity-tabs border-0" id="activityTab" role="tablist">
                            <li class="nav-item active" role="presentation">
                                <a class="nav-link active" id="comments-tab" data-toggle="tab" href="#comments-panel" role="tab">
                                    <i class="fa fa-comments-o me-1"></i>
                                    Bình luận ({{ count($comments ?? []) }})
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="worklogs-tab" data-toggle="tab" href="#worklogs-panel" role="tab">
                                    <i class="fa fa-clock-o me-1"></i>
                                    Nhật ký làm việc ({{ count($worklogs ?? []) }})
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="edit-card-body">
                        <div class="tab-content" id="activityTabContent">
                            <!-- TAB 1: Comments with CKEditor 4.22.1 -->
                            <div class="tab-pane fade in active" id="comments-panel" role="tabpanel">
                                <!-- New Comment Form with CKEditor -->
                                <form method="POST" action="{{ route('comments.store') }}" class="tp-comment-form mb-4 w-100" id="task-comment-form">
                                    @csrf
                                    <input type="hidden" name="commentable_type" value="ProjectsManager\Task">
                                    <input type="hidden" name="commentable_id" value="{{ $task->id }}">

                                    <div class="d-flex gap-3 align-items-start w-100">
                                        <div class="user-avatar-circle flex-shrink-0">
                                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div class="flex-grow-1 w-100">
                                            <label class="fw-bold m-0 mb-1 font-size-13 text-dark d-block">Thêm bình luận mới</label>
                                            <textarea
                                                id="comment-editor"
                                                name="body"
                                                class="edit-textarea comment-textarea w-100"
                                                rows="5"
                                                placeholder="Soạn bình luận, chèn ảnh, trao đổi tiến độ..."
                                                required
                                            ></textarea>

                                            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                                                <span class="text-muted small">
                                                    <i class="fa fa-paperclip me-1"></i>Hỗ trợ định dạng văn bản phong phú và hình ảnh qua CKEditor.
                                                </span>
                                                <button type="submit" class="tp-btn tp-btn-primary">
                                                    <i class="fa fa-paper-plane" style="color: #ffffff !important;"></i>
                                                    <span>Gửi bình luận</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <!-- Comments Feed -->
                                <div class="tp-comments-feed w-100">
                                    @forelse($comments as $comment)
                                        <div class="tp-comment-item w-100">
                                            <div class="user-avatar-circle flex-shrink-0">
                                                {{ strtoupper(substr($comment->user->name ?? 'U', 0, 2)) }}
                                            </div>
                                            <div class="comment-content-wrap flex-grow-1 w-100">
                                                <div class="comment-header">
                                                    <strong class="user-name">{{ $comment->user->name ?? 'Thành viên' }}</strong>
                                                    <span class="comment-time" title="{{ $comment->created_at }}">
                                                        <i class="fa fa-clock-o me-1"></i>
                                                        {{ $comment->created_at ? $comment->created_at->diffForHumans() : '' }}
                                                    </span>
                                                </div>
                                                <div class="comment-body ck-content-body w-100">
                                                    {!! $comment->body !!}
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="tp-empty-activity">
                                            <i class="fa fa-comments-o font-size-24 text-muted mb-2"></i>
                                            <p>Chưa có bình luận nào cho công việc này. Hãy là người đầu tiên thảo luận!</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- TAB 2: Worklogs Section -->
                            <div class="tab-pane fade" id="worklogs-panel" role="tabpanel">
                                <!-- Quick Log Form -->
                                <form method="POST" action="{{ route('tasks.worklog', $task) }}" class="tp-worklog-form mb-4">
                                    @csrf
                                    <h5 class="form-title mb-3">Ghi nhận thời gian làm việc</h5>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-3">
                                            <label class="small text-muted mb-1">Người thực hiện</label>
                                            @php
                                                $memberOptions = collect();
                                                if ($task->assignee) {
                                                    $memberOptions->push($task->assignee);
                                                }
                                                $memberOptions = $memberOptions->merge($task->project?->users ?? collect())->unique('id')->filter();
                                            @endphp
                                            <select name="assignee_id" class="edit-input">
                                                <option value="">-- Mặc định (tôi) --</option>
                                                @foreach($memberOptions as $member)
                                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small text-muted mb-1">Bắt đầu</label>
                                            <input type="datetime-local" name="started_at" class="edit-input">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small text-muted mb-1">Kết thúc</label>
                                            <input type="datetime-local" name="ended_at" class="edit-input">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small text-muted mb-1">Số giờ</label>
                                            <input type="number" name="hours" class="edit-input" min="0.1" max="24" step="0.25" placeholder="Ví dụ: 2.5" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small text-muted mb-1">Ngày làm việc</label>
                                            <input type="date" name="logged_at" class="edit-input" value="{{ now()->format('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-end mt-2">
                                        <div class="col-md-10">
                                            <label class="small text-muted mb-1">Ghi chú công việc</label>
                                            <input type="text" name="description" class="edit-input" placeholder="Mô tả công việc đã xử lý...">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="tp-btn tp-btn-primary w-100" style="height: 38px;">
                                                <i class="fa fa-plus-circle" style="color: #ffffff !important;"></i>
                                                <span>Ghi giờ</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Worklog History List -->
                                <div class="tp-worklog-history">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="m-0 font-weight-bold">Lịch sử Worklog</h5>
                                        <span class="total-hours-badge">
                                            Tổng cộng: <strong>{{ number_format($worklogs->sum('hours'), 2) }}h</strong>
                                        </span>
                                    </div>

                                    @forelse($worklogs as $worklog)
                                        <div class="tp-worklog-row">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa fa-calendar-o text-muted"></i>
                                                <span>{{ $worklog->logged_at ? $worklog->logged_at->format('d/m/Y') : '' }}</span>
                                            </div>
                                            <div class="worklog-hours">
                                                {{ number_format($worklog->hours, 2) }}h
                                            </div>
                                            <div class="worklog-desc">
                                                {{ $worklog->description ?: 'Không có mô tả' }}
                                            </div>
                                            <div class="worklog-user">
                                                <i class="fa fa-user-circle-o me-1 text-muted"></i>
                                                {{ $worklog->assignee->name ?? $worklog->user->name ?? 'Thành viên' }}
                                            </div>
                                        </div>
                                    @empty
                                        <div class="tp-empty-activity">
                                            <i class="fa fa-clock-o font-size-24 text-muted mb-2"></i>
                                            <p>Chưa có nhật ký thời gian làm việc được ghi nhận.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column (~30%) -->
            <div class="edit-col">
                <!-- 1. Status & Properties Card -->
                <div class="edit-card mb-4">
                    <div class="edit-card-header">
                        <h2 class="edit-card-title">
                            <i class="fa fa-sliders"></i>
                            Trạng thái & Phân loại
                        </h2>
                    </div>
                    <div class="edit-card-body">
                        <div class="sidebar-meta-group">
                            <!-- Status Selector -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Trạng thái</span>
                                <div class="meta-value">
                                    <div class="jira-inline-badge-wrap">
                                        <div id="status-badge-view" class="tp-badge font-size-13 px-3 py-1 {{ $canEdit ? 'clickable' : '' }}" style="background-color: {{ $currStatus['color'] ?? '#3b82f6' }}20; color: {{ $currStatus['color'] ?? '#3b82f6' }}; border: 1px solid {{ $currStatus['color'] ?? '#3b82f6' }};" @if($canEdit) onclick="toggleInlineEdit('status')" @endif title="Bấm để đổi trạng thái">
                                            <i class="fa {{ $currStatus['icon'] ?? 'fa-circle-o' }}" id="status-icon-el"></i>
                                            <span id="status-text-el">{{ $currStatus['name'] }}</span>
                                            @if($canEdit)<i class="fa fa-caret-down ms-1"></i>@endif
                                        </div>
                                        @if($canEdit)
                                        <select id="status-select-el" class="edit-select jira-inline-select" style="display: none;" onchange="changeTaskStatus(this.value)" onblur="cancelInlineEdit('status')">
                                            @foreach($wfStatuses as $st)
                                                <option value="{{ $st['key'] }}" {{ $task->status == $st['key'] ? 'selected' : '' }}>
                                                    {{ $st['name'] }} {{ in_array($st['key'], $allowedTargetKeys, true) || $st['key'] === $task->status ? '' : '(Cần quy tắc)' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Issue Type Selector -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Loại Issue</span>
                                <div class="meta-value">
                                    <div class="jira-inline-badge-wrap">
                                        <div id="type-badge-view" class="tp-badge tp-badge-type-{{ $task->issue_type ?? 'task' }} {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('type')" @endif title="Bấm để đổi loại issue">
                                            <i class="fa {{ $typeIcons[$task->issue_type] ?? 'fa-check-square-o' }}" id="type-icon-el"></i>
                                            <span id="type-text-el">{{ $typeLabels[$task->issue_type] ?? 'Task' }}</span>
                                            @if($canEdit)<i class="fa fa-caret-down ms-1"></i>@endif
                                        </div>
                                        @if($canEdit)
                                        <select id="type-select-el" class="edit-select jira-inline-select" style="display: none;" onchange="saveInlineField('issue_type', this.value)" onblur="cancelInlineEdit('type')">
                                            <option value="task" {{ $task->issue_type == 'task' ? 'selected' : '' }}>Task</option>
                                            <option value="bug" {{ $task->issue_type == 'bug' ? 'selected' : '' }}>Bug</option>
                                            <option value="story" {{ $task->issue_type == 'story' ? 'selected' : '' }}>Story</option>
                                            <option value="epic" {{ $task->issue_type == 'epic' ? 'selected' : '' }}>Epic</option>
                                            <option value="subtask" {{ $task->issue_type == 'subtask' ? 'selected' : '' }}>Sub-task</option>
                                        </select>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Priority Selector -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Độ ưu tiên</span>
                                <div class="meta-value">
                                    <div class="jira-inline-badge-wrap">
                                        <div id="priority-badge-view" class="tp-badge {{ $pInfo['class'] }} {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('priority')" @endif title="Bấm để đổi độ ưu tiên">
                                            <i class="fa {{ $pInfo['icon'] }}" id="priority-icon-el"></i>
                                            <span id="priority-text-el">{{ $pInfo['label'] }}</span>
                                            @if($canEdit)<i class="fa fa-caret-down ms-1"></i>@endif
                                        </div>
                                        @if($canEdit)
                                        <select id="priority-select-el" class="edit-select jira-inline-select" style="display: none;" onchange="saveInlineField('priority', this.value)" onblur="cancelInlineEdit('priority')">
                                            <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Thấp</option>
                                            <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Trung bình</option>
                                            <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>Cao / Gấp</option>
                                        </select>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. People & Assignment Card -->
                <div class="edit-card mb-4">
                    <div class="edit-card-header">
                        <h2 class="edit-card-title">
                            <i class="fa fa-users"></i>
                            Phân công & Thành viên
                        </h2>
                    </div>
                    <div class="edit-card-body">
                        <div class="sidebar-meta-group">
                            <!-- Assignee -->
                            <div class="sidebar-meta-item">
                                <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                    <span class="meta-label mb-0">Người thực hiện</span>
                                    @if($canEdit)
                                        @include('partials.ai_assignee_button', ['task' => $task, 'mode' => 'save', 'compact' => true])
                                    @endif
                                </div>
                                <div class="meta-value w-100">
                                    <div id="assignee-view-wrap" class="jira-editable-field {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('assignee')" @endif title="Bấm để đổi người thực hiện">
                                        <div id="assignee-display">
                                            @if($task->assignee)
                                                <div class="user-pill">
                                                    <div class="user-avatar-sm">
                                                        {{ strtoupper(substr($task->assignee->name ?? 'U', 0, 2)) }}
                                                    </div>
                                                    <span>{{ $task->assignee->full_name ?? $task->assignee->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted"><i class="fa fa-user-times me-1"></i>Chưa phân công</span>
                                            @endif
                                        </div>
                                        @if($canEdit)<i class="fa fa-pencil jira-hover-pencil text-muted small ms-1"></i>@endif
                                    </div>

                                    @if($canEdit)
                                    <select id="assignee-select-el" class="edit-select jira-inline-select" style="display: none;" onchange="saveInlineField('assignee_id', this.value)" onblur="cancelInlineEdit('assignee')">
                                        <option value="">Chưa phân công</option>
                                        @foreach($users ?? [] as $u)
                                            <option value="{{ $u->id }}" {{ $task->assignee_id == $u->id ? 'selected' : '' }}>
                                                👤 {{ $u->full_name ?? $u->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                            </div>

                            <!-- Reporter -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Người báo cáo / Tạo</span>
                                <div class="meta-value">
                                    <div id="reporter-view-wrap" class="jira-editable-field {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('reporter')" @endif title="Bấm để đổi người báo cáo">
                                        <div id="reporter-display">
                                            @if($task->reporter)
                                                <div class="user-pill">
                                                    <div class="user-avatar-sm" style="background-color: #8b5cf6;">
                                                        {{ strtoupper(substr($task->reporter->name ?? 'U', 0, 2)) }}
                                                    </div>
                                                    <span>{{ $task->reporter->full_name ?? $task->reporter->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted"><i class="fa fa-user-o me-1"></i>Chưa cập nhật</span>
                                            @endif
                                        </div>
                                        @if($canEdit)<i class="fa fa-pencil jira-hover-pencil text-muted small ms-1"></i>@endif
                                    </div>

                                    @if($canEdit)
                                    <select id="reporter-select-el" class="edit-select jira-inline-select" style="display: none;" onchange="saveInlineField('reporter_id', this.value)" onblur="cancelInlineEdit('reporter')">
                                        <option value="">Chưa chọn</option>
                                        @foreach($reporters ?? [] as $r)
                                            <option value="{{ $r->id }}" {{ $task->reporter_id == $r->id ? 'selected' : '' }}>
                                                👤 {{ $r->full_name ?? $r->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Planning & Agile Card -->
                <div class="edit-card mb-4">
                    <div class="edit-card-header">
                        <h2 class="edit-card-title">
                            <i class="fa fa-compass"></i>
                            Kế hoạch & Agile
                        </h2>
                    </div>
                    <div class="edit-card-body">
                        <div class="sidebar-meta-group">
                            <!-- Project -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Dự án</span>
                                <div class="meta-value">
                                    <a href="{{ route('projects.show', $task->project_id) }}" class="project-link font-weight-bold" style="color: #2563eb; text-decoration: none;">
                                        <i class="fa fa-folder-o me-1"></i>
                                        {{ $task->project->name ?? 'N/A' }}
                                    </a>
                                </div>
                            </div>

                            <!-- Epic -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Epic chứa</span>
                                <div class="meta-value">
                                    <div id="parent-view-wrap" class="jira-editable-field {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('parent')" @endif title="Bấm để đổi Epic">
                                        <div id="parent-display">
                                            @if($task->parent)
                                                <span class="epic-badge">
                                                    ⚡ {{ $task->parent->issue_key ?? 'EPIC-'.$task->parent->id }} - {{ $task->parent->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">Không thuộc Epic</span>
                                            @endif
                                        </div>
                                        @if($canEdit)<i class="fa fa-pencil jira-hover-pencil text-muted small ms-1"></i>@endif
                                    </div>

                                    @if($canEdit)
                                    <select id="parent-select-el" class="edit-select jira-inline-select" style="display: none;" onchange="saveInlineField('parent_id', this.value)" onblur="cancelInlineEdit('parent')">
                                        <option value="">Không thuộc Epic</option>
                                        @foreach($epics ?? [] as $epic)
                                            <option value="{{ $epic->id }}" {{ $task->parent_id == $epic->id ? 'selected' : '' }}>
                                                ⚡ {{ $epic->issue_key ?? 'EPIC-'.$epic->id }} - {{ $epic->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                            </div>

                            <!-- Sprint -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Sprint</span>
                                <div class="meta-value">
                                    <div id="sprint-view-wrap" class="jira-editable-field {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('sprint')" @endif title="Bấm để đổi Sprint">
                                        <div id="sprint-display">
                                            @if($task->sprintRelation)
                                                <span class="sprint-pill">
                                                    🏃 {{ $task->sprintRelation->name }}
                                                </span>
                                            @elseif($task->sprint)
                                                <span class="sprint-pill">
                                                    🏃 {{ $task->sprint }}
                                                </span>
                                            @else
                                                <span class="text-muted">Backlog (Chưa vào Sprint)</span>
                                            @endif
                                        </div>
                                        @if($canEdit)<i class="fa fa-pencil jira-hover-pencil text-muted small ms-1"></i>@endif
                                    </div>

                                    @if($canEdit)
                                    <select id="sprint-select-el" class="edit-select jira-inline-select" style="display: none;" onchange="saveInlineField('sprint_id', this.value)" onblur="cancelInlineEdit('sprint')">
                                        <option value="">Backlog</option>
                                        @foreach($sprints ?? [] as $sprint)
                                            <option value="{{ $sprint->id }}" {{ $task->sprint_id == $sprint->id ? 'selected' : '' }}>
                                                🏃 {{ $sprint->name }} ({{ $sprint->status }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                            </div>

                            <!-- Story Points -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Story Points</span>
                                <div class="meta-value">
                                    <div id="pts-view-wrap" class="jira-editable-field {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('pts')" @endif title="Bấm để đổi Story Points">
                                        <span id="pts-display" class="points-badge">
                                            {{ $task->story_points ?? '0' }} pts
                                        </span>
                                        @if($canEdit)<i class="fa fa-pencil jira-hover-pencil text-muted small ms-1"></i>@endif
                                    </div>

                                    @if($canEdit)
                                    <div id="pts-edit-wrap" style="display: none;" class="d-flex align-items-center gap-1">
                                        <input type="number" id="input-story-points" step="0.5" min="0" class="edit-input input-sm" style="width: 90px;" value="{{ $task->story_points }}">
                                        <button type="button" class="tp-btn tp-btn-primary btn-xs" onclick="saveInlineField('story_points', document.getElementById('input-story-points').value)"><i class="fa fa-check"></i></button>
                                        <button type="button" class="tp-btn tp-btn-light btn-xs" onclick="cancelInlineEdit('pts')"><i class="fa fa-times"></i></button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Dates & Labels Card -->
                <div class="edit-card mb-4">
                    <div class="edit-card-header">
                        <h2 class="edit-card-title">
                            <i class="fa fa-calendar-check-o"></i>
                            Thời gian & Nhãn
                        </h2>
                    </div>
                    <div class="edit-card-body">
                        <div class="sidebar-meta-group">
                            <!-- Start Date -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Ngày bắt đầu</span>
                                <div class="meta-value">
                                    <div id="start-date-view-wrap" class="jira-editable-field {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('start_date')" @endif title="Bấm để đổi ngày bắt đầu">
                                        <span id="start-date-display">
                                            <i class="fa fa-calendar-check-o text-muted me-1"></i>
                                            {{ $task->start_date ? $task->start_date->format('d/m/Y') : 'Chưa đặt' }}
                                        </span>
                                        @if($canEdit)<i class="fa fa-pencil jira-hover-pencil text-muted small ms-1"></i>@endif
                                    </div>

                                    @if($canEdit)
                                    <input type="date" id="input-start-date" class="edit-input jira-inline-select" style="display: none;" value="{{ optional($task->start_date)->format('Y-m-d') }}" onchange="saveInlineField('start_date', this.value)" onblur="cancelInlineEdit('start_date')">
                                    @endif
                                </div>
                            </div>

                            <!-- Due Date -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Hạn hoàn thành (Due date)</span>
                                <div class="meta-value">
                                    <div id="due-date-view-wrap" class="jira-editable-field {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('due_date')" @endif title="Bấm để đổi hạn hoàn thành">
                                        <span id="due-date-display">
                                            @if($task->due_date || $task->end_date)
                                                @php
                                                    $dueDate = $task->due_date ?? $task->end_date;
                                                    $isOverdue = $dueDate->isPast() && !in_array($task->status, ['done', 'completed', 'resolved', 'closed'], true);
                                                @endphp
                                                <span class="{{ $isOverdue ? 'text-danger font-weight-bold' : '' }}">
                                                    <i class="fa fa-calendar-times-o me-1"></i>
                                                    {{ $dueDate->format('d/m/Y') }}
                                                    @if($isOverdue)
                                                        <small class="badge bg-danger ms-1">Quá hạn</small>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">Chưa đặt</span>
                                            @endif
                                        </span>
                                        @if($canEdit)<i class="fa fa-pencil jira-hover-pencil text-muted small ms-1"></i>@endif
                                    </div>

                                    @if($canEdit)
                                    <input type="date" id="input-due-date" class="edit-input jira-inline-select" style="display: none;" value="{{ optional($task->due_date ?? $task->end_date)->format('Y-m-d') }}" onchange="saveInlineField('due_date', this.value)" onblur="cancelInlineEdit('due_date')">
                                    @endif
                                </div>
                            </div>

                            <!-- Labels -->
                            <div class="sidebar-meta-item">
                                <span class="meta-label">Nhãn (Labels)</span>
                                <div class="meta-value">
                                    <div id="labels-view-wrap" class="d-flex align-items-center gap-1 flex-wrap {{ $canEdit ? 'clickable' : '' }}" @if($canEdit) onclick="toggleInlineEdit('labels')" @endif title="Bấm để sửa nhãn">
                                        <div id="labels-badges-list" class="d-flex align-items-center gap-1 flex-wrap">
                                            @if($task->labels)
                                                @foreach(explode(',', $task->labels) as $label)
                                                    @if(trim($label))
                                                        <span class="tag-item">#{{ trim($label) }}</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-muted small">Chưa gắn nhãn</span>
                                            @endif
                                        </div>
                                        @if($canEdit)<i class="fa fa-pencil jira-hover-pencil text-muted small ms-1"></i>@endif
                                    </div>

                                    @if($canEdit)
                                    <div id="labels-edit-wrap" style="display: none;" class="d-flex align-items-center gap-1 mt-1">
                                        <input type="text" id="input-labels" class="edit-input input-sm" style="width: 180px;" value="{{ $task->labels }}" placeholder="Frontend, API...">
                                        <button type="button" class="tp-btn tp-btn-primary btn-xs" onclick="saveInlineField('labels', document.getElementById('input-labels').value)"><i class="fa fa-check"></i></button>
                                        <button type="button" class="tp-btn tp-btn-light btn-xs" onclick="cancelInlineEdit('labels')"><i class="fa fa-times"></i></button>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Timestamps -->
                            <div class="sidebar-meta-item pt-2 border-top">
                                <span class="text-muted small">Tạo ngày: {{ $task->created_at ? $task->created_at->format('d/m/Y H:i') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Local Open-Source CKEditor 4.22.1
    function initAllCKEditors() {
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.config.versionCheck = false;

            if (document.getElementById('comment-editor') && !CKEDITOR.instances['comment-editor']) {
                CKEDITOR.replace('comment-editor', {
                    height: 140,
                    width: '100%',
                    versionCheck: false,
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
                        { name: 'links', items: ['Link', 'Unlink'] },
                        { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                        { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                        { name: 'colors', items: ['TextColor', 'BGColor'] },
                        { name: 'tools', items: ['Maximize'] }
                    ]
                });
            }

            if (document.getElementById('task-description-editor') && !CKEDITOR.instances['task-description-editor']) {
                CKEDITOR.replace('task-description-editor', {
                    height: 200,
                    width: '100%',
                    versionCheck: false,
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
                        { name: 'links', items: ['Link', 'Unlink'] },
                        { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                        { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                        { name: 'colors', items: ['TextColor', 'BGColor'] },
                        { name: 'tools', items: ['Maximize'] }
                    ]
                });
            }
        } else {
            setTimeout(initAllCKEditors, 100);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllCKEditors);
    } else {
        initAllCKEditors();
    }

    var commentForm = document.getElementById('task-comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function() {
            if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['comment-editor']) {
                CKEDITOR.instances['comment-editor'].updateElement();
            }
        });
    }

    function saveDescriptionCKEditor() {
        var descVal = '';
        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['task-description-editor']) {
            descVal = CKEDITOR.instances['task-description-editor'].getData();
        } else if (document.getElementById('task-description-editor')) {
            descVal = document.getElementById('task-description-editor').value;
        }
        saveInlineField('description', descVal);
    }

    function showToast(msg) {
        var toast = document.getElementById('tp-task-toast');
        var text = document.getElementById('tp-toast-message');
        if (toast && text) {
            text.textContent = msg;
            toast.style.display = 'flex';
            setTimeout(function() {
                toast.style.display = 'none';
            }, 3000);
        }
    }

    function changeTaskStatus(newStatus) {
        moveTaskWithPayload(newStatus, {});
    }

    function moveTaskWithPayload(newStatus, payload) {
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch("/tasks/{{ $task->id }}/move", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(Object.assign({ status: newStatus }, payload))
        })
        .then(function(res) {
            return res.json().then(function(data) {
                return { ok: res.ok, data: data };
            });
        })
        .then(function(result) {
            if (result.ok && result.data.success) {
                showToast(result.data.message || 'Đã chuyển trạng thái thành công.');
                setTimeout(function() { window.location.reload(); }, 400);
            } else {
                var data = result.data || {};
                if (data.gate_type === 'approval_gate') {
                    var sig = prompt((data.message || 'Cần phê duyệt từ Tech Lead / Khách hàng') + '\n\nNhập chữ ký duyệt để tiếp tục:');
                    if (sig && sig.trim()) {
                        moveTaskWithPayload(newStatus, { approval_signature: sig.trim() });
                    }
                } else if (data.gate_type === 'validation_rule' && data.required_action === 'worklog') {
                    var hours = prompt((data.message || 'Cần ghi nhận thời gian làm việc (Worklog)') + '\n\nNhập số giờ làm việc thực tế:', '1.0');
                    if (hours && parseFloat(hours) > 0) {
                        moveTaskWithPayload(newStatus, { worklog_hours: parseFloat(hours) });
                    }
                } else if (data.gate_type === 'validation_rule' && data.required_action === 'test_result') {
                    var tr = prompt((data.message || 'Cần kết quả kiểm thử (Test Result)') + '\n\nGhi nhận kết quả kiểm thử (Passed / Result):', 'Passed');
                    if (tr && tr.trim()) {
                        moveTaskWithPayload(newStatus, { test_status: 'passed', test_result: tr.trim() });
                    }
                } else {
                    alert(data.message || 'Không thể chuyển trạng thái công việc.');
                }
            }
        })
        .catch(function(err) {
            console.error('Status transition error:', err);
            alert('Có lỗi xảy ra khi kết nối máy chủ.');
        });
    }

    function toggleInlineEdit(field) {
        if (field === 'title') {
            document.getElementById('jira-title-view').style.display = 'none';
            document.getElementById('jira-title-edit').style.display = 'block';
            document.getElementById('input-task-name').focus();
        } else if (field === 'type') {
            document.getElementById('type-badge-view').style.display = 'none';
            document.getElementById('type-select-el').style.display = 'inline-block';
            document.getElementById('type-select-el').focus();
        } else if (field === 'priority') {
            document.getElementById('priority-badge-view').style.display = 'none';
            document.getElementById('priority-select-el').style.display = 'inline-block';
            document.getElementById('priority-select-el').focus();
        } else if (field === 'status') {
            document.getElementById('status-badge-view').style.display = 'none';
            document.getElementById('status-select-el').style.display = 'inline-block';
            document.getElementById('status-select-el').focus();
        } else if (field === 'labels') {
            document.getElementById('labels-view-wrap').style.display = 'none';
            document.getElementById('labels-edit-wrap').style.display = 'flex';
            document.getElementById('input-labels').focus();
        } else if (field === 'assignee') {
            document.getElementById('assignee-view-wrap').style.display = 'none';
            document.getElementById('assignee-select-el').style.display = 'block';
            document.getElementById('assignee-select-el').focus();
        } else if (field === 'reporter') {
            document.getElementById('reporter-view-wrap').style.display = 'none';
            document.getElementById('reporter-select-el').style.display = 'block';
            document.getElementById('reporter-select-el').focus();
        } else if (field === 'parent') {
            document.getElementById('parent-view-wrap').style.display = 'none';
            document.getElementById('parent-select-el').style.display = 'block';
            document.getElementById('parent-select-el').focus();
        } else if (field === 'sprint') {
            document.getElementById('sprint-view-wrap').style.display = 'none';
            document.getElementById('sprint-select-el').style.display = 'block';
            document.getElementById('sprint-select-el').focus();
        } else if (field === 'pts') {
            document.getElementById('pts-view-wrap').style.display = 'none';
            document.getElementById('pts-edit-wrap').style.display = 'flex';
            document.getElementById('input-story-points').focus();
        } else if (field === 'start_date') {
            document.getElementById('start-date-view-wrap').style.display = 'none';
            document.getElementById('input-start-date').style.display = 'block';
            document.getElementById('input-start-date').focus();
        } else if (field === 'due_date') {
            document.getElementById('due-date-view-wrap').style.display = 'none';
            document.getElementById('input-due-date').style.display = 'block';
            document.getElementById('input-due-date').focus();
        }
    }

    function cancelInlineEdit(field) {
        if (field === 'title') {
            document.getElementById('jira-title-view').style.display = 'block';
            document.getElementById('jira-title-edit').style.display = 'none';
        } else if (field === 'type') {
            document.getElementById('type-badge-view').style.display = 'inline-flex';
            document.getElementById('type-select-el').style.display = 'none';
        } else if (field === 'priority') {
            document.getElementById('priority-badge-view').style.display = 'inline-flex';
            document.getElementById('priority-select-el').style.display = 'none';
        } else if (field === 'status') {
            document.getElementById('status-badge-view').style.display = 'inline-flex';
            document.getElementById('status-select-el').style.display = 'none';
        } else if (field === 'labels') {
            document.getElementById('labels-view-wrap').style.display = 'flex';
            document.getElementById('labels-edit-wrap').style.display = 'none';
        } else if (field === 'assignee') {
            document.getElementById('assignee-view-wrap').style.display = 'flex';
            document.getElementById('assignee-select-el').style.display = 'none';
        } else if (field === 'reporter') {
            document.getElementById('reporter-view-wrap').style.display = 'flex';
            document.getElementById('reporter-select-el').style.display = 'none';
        } else if (field === 'parent') {
            document.getElementById('parent-view-wrap').style.display = 'flex';
            document.getElementById('parent-select-el').style.display = 'none';
        } else if (field === 'sprint') {
            document.getElementById('sprint-view-wrap').style.display = 'flex';
            document.getElementById('sprint-select-el').style.display = 'none';
        } else if (field === 'pts') {
            document.getElementById('pts-view-wrap').style.display = 'flex';
            document.getElementById('pts-edit-wrap').style.display = 'none';
        } else if (field === 'start_date') {
            document.getElementById('start-date-view-wrap').style.display = 'flex';
            document.getElementById('input-start-date').style.display = 'none';
        } else if (field === 'due_date') {
            document.getElementById('due-date-view-wrap').style.display = 'flex';
            document.getElementById('input-due-date').style.display = 'none';
        }
    }

    function saveInlineField(fieldName, fieldValue) {
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var payload = {};
        payload[fieldName] = fieldValue;

        fetch("{{ route('tasks.update', $task->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(Object.assign({ _method: 'PUT' }, payload))
        })
        .then(function(res) { 
            return res.json().then(function(data) {
                return { ok: res.ok, data: data };
            });
        })
        .then(function(result) {
            if (result.ok && result.data.success) {
                showToast('Đã lưu thay đổi.');
                setTimeout(function() { window.location.reload(); }, 400);
            } else {
                alert(result.data.message || 'Có lỗi xảy ra khi cập nhật.');
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Có lỗi kết nối máy chủ.');
        });
    }
</script>
@include('partials.ai_assignee_assets')
@endsection