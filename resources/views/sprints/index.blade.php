@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/backlog-agile.css') }}?v={{ file_exists(public_path('css/backlog-agile.css')) ? filemtime(public_path('css/backlog-agile.css')) : time() }}">
@endpush

@section('content')
<div class="tp-backlog-page">
    <!-- Project Header & Nav Tabs -->
    <div class="tp-project-header">
        <div class="tp-project-title-area">
            <div class="tp-project-title-left">
                <span class="tp-project-code-badge">{{ $project->project_code ?: 'TP-' . $project->id }}</span>
                <div>
                    <h1 class="tp-project-title">{{ $project->name }}</h1>
                    <div style="font-size: 13px; color: #64748b; margin-top: 2px;">
                        Quản lý Backlog, lập kế hoạch Sprint và phân chia công việc theo phương pháp Agile Scrum
                    </div>
                </div>
            </div>
            <div class="tp-project-actions">
                <button type="button" class="btn btn-primary tp-btn-primary" data-toggle="modal" data-target="#createSprintModal" id="btn-open-create-sprint">
                    <i class="fa fa-plus me-1"></i> Tạo Sprint mới
                </button>
                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-default" style="font-weight: 600; border-radius: 8px;">
                    <i class="fa fa-plus me-1"></i> Tạo Task chi tiết
                </a>
            </div>
        </div>

        <!-- Unified Project Nav Tabs -->
        @include('projects.partials.nav', ['project' => $project, 'active' => 'sprints'])
    </div>

    <!-- Agile KPI Summary Grid -->
    <div class="tp-agile-kpi-grid">
        <div class="tp-kpi-card kpi-blue">
            <div class="tp-kpi-label">
                <span>Product Backlog</span>
                <i class="fa fa-inbox text-primary"></i>
            </div>
            <div class="tp-kpi-value" id="kpi-backlog-count">{{ $backlog->count() }}</div>
            <div class="tp-kpi-subtext">Tổng <strong>{{ $totalBacklogPoints }}</strong> Story Points tồn đọng</div>
        </div>

        <div class="tp-kpi-card kpi-green">
            <div class="tp-kpi-label">
                <span>Active Sprint (Đang chạy)</span>
                <i class="fa fa-bolt text-success"></i>
            </div>
            @if($activeSprint)
                <div class="tp-kpi-value" style="font-size: 22px; margin-top: 10px;">{{ $activeSprint->name }}</div>
                <div class="tp-kpi-subtext">
                    {{ $activeSprint->tasks->count() }} task (<strong>{{ $activeSprintPoints }}</strong> SP) • 
                    {{ $activeSprintDonePoints }}/{{ $activeSprintPoints }} SP hoàn thành
                </div>
            @else
                <div class="tp-kpi-value" style="font-size: 20px; color: #94a3b8; margin-top: 12px;">Chưa kích hoạt</div>
                <div class="tp-kpi-subtext">Hãy chọn một Sprint và nhấn "Bắt đầu Sprint"</div>
            @endif
        </div>

        <div class="tp-kpi-card kpi-purple">
            <div class="tp-kpi-label">
                <span>Tổng số Sprint</span>
                <i class="fa fa-flag text-purple"></i>
            </div>
            <div class="tp-kpi-value">{{ $project->sprints->count() }}</div>
            <div class="tp-kpi-subtext">
                {{ $project->sprints->where('status', 'planned')->count() }} kế hoạch • 
                {{ $project->sprints->where('status', 'closed')->count() }} hoàn thành
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="tp-backlog-toolbar">
        <div class="tp-filter-group">
            <div class="tp-backlog-search">
                <i class="fa fa-search"></i>
                <input type="text" id="backlog-search-input" placeholder="Tìm kiếm công việc, mã issue... (ví dụ: TP-1)">
            </div>

            <select class="tp-filter-select" id="filter-issue-type">
                <option value="">Tất cả loại issue</option>
                <option value="story">Story</option>
                <option value="task">Task</option>
                <option value="bug">Bug</option>
                <option value="epic">Epic</option>
            </select>

            <select class="tp-filter-select" id="filter-priority">
                <option value="">Tất cả độ ưu tiên</option>
                <option value="khancap">Khẩn cấp</option>
                <option value="cao">Cao</option>
                <option value="trungbinh">Trung bình</option>
                <option value="thap">Thấp</option>
            </select>
        </div>

        <div style="font-size: 12px; color: #64748b; font-weight: 500;">
            <i class="fa fa-arrows me-1"></i> Kéo thả task để phân bổ vào Sprint hoặc Backlog
        </div>
    </div>

    <!-- 1. ACTIVE SPRINT SECTION -->
    @if($activeSprint)
        @php
            $aTotal = $activeSprint->tasks->count();
            $aDone = $activeSprint->tasks->filter(fn ($t) => in_array(strtolower($t->status ?? ''), $doneStatuses))->count();
            $aPercent = $aTotal ? (int) round(($aDone / $aTotal) * 100) : 0;
            $aPoints = $activeSprint->tasks->sum('story_points');
        @endphp
        <div class="tp-sprint-container tp-drop-zone" data-sprint-id="{{ $activeSprint->id }}" id="sprint-box-{{ $activeSprint->id }}">
            <div class="tp-sprint-header">
                <div class="tp-sprint-header-left">
                    <span class="tp-sprint-title">
                        <i class="fa fa-play-circle text-success"></i>
                        {{ $activeSprint->name }}
                    </span>
                    <span class="tp-sprint-badge badge-active">Đang chạy</span>
                    <span class="tp-sprint-dates">
                        <i class="fa fa-calendar-o"></i>
                        {{ $activeSprint->starts_at ? $activeSprint->starts_at->format('d/m/Y') : 'Chưa đặt' }}
                        –
                        {{ $activeSprint->ends_at ? $activeSprint->ends_at->format('d/m/Y') : 'Chưa đặt' }}
                    </span>
                    <span style="font-size: 12.5px; color: #64748b; font-weight: 600;">
                        (<span class="sprint-count-label">{{ $aTotal }}</span> task • <span class="sprint-points-label">{{ $aPoints }}</span> SP)
                    </span>
                </div>

                <div class="tp-sprint-header-right">
                    <a href="{{ route('projects.board', $project) }}" class="btn btn-sm btn-default" style="border-radius: 6px; font-weight: 600;">
                        <i class="fa fa-columns me-1"></i> Xem trên Board
                    </a>
                    <form method="POST" action="{{ route('sprints.close', [$project, $activeSprint]) }}" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn hoàn thành và đóng Sprint này?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; font-weight: 600;">
                            <i class="fa fa-check-circle me-1"></i> Hoàn thành Sprint
                        </button>
                    </form>
                </div>
            </div>

            @if($activeSprint->goal)
                <div class="tp-sprint-goal-banner">
                    <i class="fa fa-bullseye"></i>
                    <strong>Mục tiêu:</strong> {{ $activeSprint->goal }}
                </div>
            @endif

            <div class="tp-sprint-progress-wrap">
                <div class="tp-sprint-progress-stats">
                    <span>Tiến độ hoàn thành: <strong>{{ $aDone }}/{{ $aTotal }} task</strong></span>
                    <strong style="color: #10b981;">{{ $aPercent }}%</strong>
                </div>
                <div class="tp-progress-bar-bg">
                    <div class="tp-progress-bar-fill" style="width: {{ $aPercent }}%;"></div>
                </div>
            </div>

            <div class="tp-task-list" id="task-list-sprint-{{ $activeSprint->id }}">
                @forelse($activeSprint->tasks as $task)
                    @include('sprints.partials.task_item', ['task' => $task, 'movableSprints' => $movableSprints, 'currentSprintId' => $activeSprint->id])
                @empty
                    <div class="tp-empty-drop-zone">
                        <i class="fa fa-inbox"></i>
                        <span>Sprint này chưa có công việc. Kéo thả task từ Backlog vào đây để phân công.</span>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- 2. PLANNED SPRINTS SECTION -->
    @foreach($project->sprints->where('status', 'planned') as $sprint)
        @php
            $pTotal = $sprint->tasks->count();
            $pPoints = $sprint->tasks->sum('story_points');
        @endphp
        <div class="tp-sprint-container tp-drop-zone" data-sprint-id="{{ $sprint->id }}" id="sprint-box-{{ $sprint->id }}">
            <div class="tp-sprint-header">
                <div class="tp-sprint-header-left">
                    <span class="tp-sprint-title">
                        <i class="fa fa-clock-o text-primary"></i>
                        {{ $sprint->name }}
                    </span>
                    <span class="tp-sprint-badge badge-planned">Kế hoạch</span>
                    <span class="tp-sprint-dates">
                        <i class="fa fa-calendar-o"></i>
                        {{ $sprint->starts_at ? $sprint->starts_at->format('d/m/Y') : 'Chưa đặt ngày' }}
                        –
                        {{ $sprint->ends_at ? $sprint->ends_at->format('d/m/Y') : 'Chưa đặt ngày' }}
                    </span>
                    <span style="font-size: 12.5px; color: #64748b; font-weight: 600;">
                        (<span class="sprint-count-label">{{ $pTotal }}</span> task • <span class="sprint-points-label">{{ $pPoints }}</span> SP)
                    </span>
                </div>

                <div class="tp-sprint-header-right">
                    <form method="POST" action="{{ route('sprints.start', [$project, $sprint]) }}" style="display: inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success" style="border-radius: 6px; font-weight: 600;">
                            <i class="fa fa-play me-1"></i> Bắt đầu Sprint
                        </button>
                    </form>
                </div>
            </div>

            @if($sprint->goal)
                <div class="tp-sprint-goal-banner">
                    <i class="fa fa-bullseye"></i>
                    <strong>Mục tiêu:</strong> {{ $sprint->goal }}
                </div>
            @endif

            <div class="tp-task-list" id="task-list-sprint-{{ $sprint->id }}">
                @forelse($sprint->tasks as $task)
                    @include('sprints.partials.task_item', ['task' => $task, 'movableSprints' => $movableSprints, 'currentSprintId' => $sprint->id])
                @empty
                    <div class="tp-empty-drop-zone">
                        <i class="fa fa-inbox"></i>
                        <span>Chưa có task trong Sprint kế hoạch này. Kéo thả task từ Backlog vào đây.</span>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach

    <!-- 3. PRODUCT BACKLOG SECTION -->
    <div class="tp-backlog-container tp-drop-zone" data-sprint-id="" id="backlog-container-box">
        <div class="tp-sprint-header" style="background: #ffffff;">
            <div class="tp-sprint-header-left">
                <span class="tp-sprint-title">
                    <i class="fa fa-database text-primary"></i>
                    Product Backlog
                </span>
                <span style="font-size: 13px; color: #64748b; font-weight: 600;">
                    (<span id="backlog-items-count">{{ $backlog->count() }}</span> công việc • <span id="backlog-points-sum">{{ $totalBacklogPoints }}</span> Story Points)
                </span>
            </div>
            <div class="tp-sprint-header-right">
                <span style="font-size: 12px; color: #64748b;">
                    Kho nhiệm vụ chưa lên kế hoạch Sprint
                </span>
            </div>
        </div>

        <!-- Task List in Backlog -->
        <div class="tp-task-list" id="task-list-backlog">
            @forelse($backlog as $task)
                @include('sprints.partials.task_item', ['task' => $task, 'movableSprints' => $movableSprints, 'currentSprintId' => null])
            @empty
                <div class="tp-empty-drop-zone" id="backlog-empty-msg">
                    <i class="fa fa-check-circle-o text-success" style="font-size: 32px;"></i>
                    <strong style="color: #1e293b; font-size: 15px;">Backlog đang trống!</strong>
                    <span>Tất cả công việc đã được đưa vào Sprint hoặc chưa có công việc mới. Hãy sử dụng form bên dưới để thêm nhanh.</span>
                </div>
            @endforelse
        </div>

        <!-- Quick Add Inline Form -->
        <div class="tp-quick-add-wrap">
            <form id="quick-add-backlog-form" class="tp-quick-add-form" method="POST" action="{{ route('sprints.tasks.quick-store', $project) }}">
                @csrf
                <i class="fa fa-plus text-primary"></i>
                <input type="text" name="name" class="tp-quick-add-input" placeholder="+ Nhập tiêu đề công việc mới và nhấn Enter để thêm vào Backlog..." required autocomplete="off">
                
                <select name="issue_type" class="tp-quick-add-select">
                    <option value="story">Story</option>
                    <option value="task" selected>Task</option>
                    <option value="bug">Bug</option>
                    <option value="epic">Epic</option>
                </select>

                <select name="priority" class="tp-quick-add-select">
                    <option value="Trung bình" selected>Ưu tiên: Trung bình</option>
                    <option value="Cao">Ưu tiên: Cao</option>
                    <option value="Khẩn cấp">Ưu tiên: Khẩn cấp</option>
                    <option value="Thấp">Ưu tiên: Thấp</option>
                </select>

                <input type="number" name="story_points" class="tp-quick-add-select" style="width: 85px;" placeholder="SP (1-13)" min="0" max="99">

                <button type="submit" class="btn btn-primary tp-btn-primary btn-sm" style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                    Thêm vào Backlog
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Tạo Sprint Mới -->
<div class="modal fade" id="createSprintModal" tabindex="-1" role="dialog" aria-labelledby="createSprintModalLabel">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('sprints.store', $project) }}" class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
            @csrf
            <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 24px;">&times;</button>
                <h4 class="modal-title" id="createSprintModalLabel" style="font-weight: 750; color: #0f172a;">
                    <i class="fa fa-flag text-primary me-2"></i> Tạo Sprint Mới
                </h4>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group mb-3">
                    <label class="control-label" style="font-weight: 600; color: #334155;">Tên Sprint <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Ví dụ: Sprint 01 - Khởi động dự án" required style="border-radius: 8px; height: 40px;">
                </div>

                <div class="form-group mb-3">
                    <label class="control-label" style="font-weight: 600; color: #334155;">Mục tiêu Sprint (Sprint Goal)</label>
                    <textarea name="goal" class="form-control" rows="3" placeholder="Mục tiêu chính cần hoàn thành trong Sprint này..." style="border-radius: 8px;"></textarea>
                </div>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label class="control-label" style="font-weight: 600; color: #334155;">Ngày bắt đầu</label>
                            <input type="date" name="starts_at" class="form-control" value="{{ now()->toDateString() }}" style="border-radius: 8px; height: 40px;">
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label class="control-label" style="font-weight: 600; color: #334155;">Ngày kết thúc</label>
                            <input type="date" name="ends_at" class="form-control" value="{{ now()->addWeeks(2)->toDateString() }}" style="border-radius: 8px; height: 40px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Hủy</button>
                <button type="submit" class="btn btn-primary tp-btn-primary" style="border-radius: 8px; font-weight: 600; padding: 7px 18px;">Tạo Sprint</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Chỉnh sửa Story Points (Planning Poker Estimation) -->
<div class="modal fade" id="storyPointsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 18px;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title" style="font-weight: 700; color: #0f172a;">
                    <i class="fa fa-calculator text-primary me-1"></i> Ước tính Story Points
                </h5>
            </div>
            <div class="modal-body text-center" style="padding: 20px;">
                <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
                    Chọn điểm ước lượng độ phức tạp (Fibonacci Scrum):
                </p>
                <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 16px;">
                    @foreach([1, 2, 3, 5, 8, 13, 21] as $sp)
                        <button type="button" class="btn btn-default btn-fibonacci" data-points="{{ $sp }}" style="width: 44px; height: 44px; border-radius: 10px; font-size: 15px; font-weight: 800; border: 1px solid #cbd5e1; color: #1e293b;">
                            {{ $sp }}
                        </button>
                    @endforeach
                </div>
                <div class="form-group mb-0">
                    <input type="number" id="custom-story-points-input" class="form-control text-center" placeholder="Hoặc nhập điểm tùy chỉnh..." min="0" max="999" style="border-radius: 8px;">
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 16px;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary btn-sm tp-btn-primary" id="btn-save-story-points">Lưu điểm</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var projectId = {{ $project->id }};

    // 1. DRAG AND DROP HANDLING
    var draggedItem = null;

    function initDragAndDrop() {
        var taskItems = document.querySelectorAll('.tp-task-item');
        var dropZones = document.querySelectorAll('.tp-drop-zone');

        taskItems.forEach(function(item) {
            item.removeEventListener('dragstart', handleDragStart);
            item.removeEventListener('dragend', handleDragEnd);
            item.addEventListener('dragstart', handleDragStart);
            item.addEventListener('dragend', handleDragEnd);
        });

        dropZones.forEach(function(zone) {
            zone.removeEventListener('dragover', handleDragOver);
            zone.removeEventListener('dragleave', handleDragLeave);
            zone.removeEventListener('drop', handleDrop);
            zone.addEventListener('dragover', handleDragOver);
            zone.addEventListener('dragleave', handleDragLeave);
            zone.addEventListener('drop', handleDrop);
        });
    }

    function handleDragStart(e) {
        draggedItem = this;
        this.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', this.dataset.taskId);
    }

    function handleDragEnd() {
        this.classList.remove('is-dragging');
        document.querySelectorAll('.tp-drop-zone').forEach(function(zone) {
            zone.classList.remove('is-drag-over');
        });
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        this.classList.add('is-drag-over');
    }

    function handleDragLeave(e) {
        if (!this.contains(e.relatedTarget)) {
            this.classList.remove('is-drag-over');
        }
    }

    function handleDrop(e) {
        e.preventDefault();
        this.classList.remove('is-drag-over');

        if (!draggedItem) return;

        var taskId = draggedItem.dataset.taskId;
        var targetSprintId = this.dataset.sprintId || null;
        var sourceList = draggedItem.closest('.tp-task-list');
        var targetList = this.querySelector('.tp-task-list');

        if (!targetList || targetList === sourceList) return;

        // Move DOM element optimistically
        targetList.appendChild(draggedItem);

        // Update select value inside the moved item
        var moveSelect = draggedItem.querySelector('.tp-move-sprint-select');
        if (moveSelect) {
            moveSelect.value = targetSprintId || '';
        }

        // Hide empty messages if any
        var emptyMsg = targetList.querySelector('.tp-empty-drop-zone');
        if (emptyMsg) emptyMsg.style.display = 'none';

        // AJAX update on server
        fetch('/projects/' + projectId + '/tasks/' + taskId + '/sprint', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ sprint_id: targetSprintId })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data.success) {
                alert('Không thể chuyển task: ' + (data.message || 'Lỗi không xác định'));
                window.location.reload();
            }
        })
        .catch(function(err) {
            console.error('Move error:', err);
        });
    }

    // 2. MOVE TASK VIA SELECT DROPDOWN
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('tp-move-sprint-select')) {
            var taskItem = e.target.closest('.tp-task-item');
            var taskId = taskItem ? taskItem.dataset.taskId : null;
            var targetSprintId = e.target.value || null;

            if (!taskId) return;

            fetch('/projects/' + projectId + '/tasks/' + taskId + '/sprint', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ sprint_id: targetSprintId })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi chuyển công việc');
                }
            })
            .catch(function(err) {
                console.error(err);
                window.location.reload();
            });
        }
    });

    // Modal Helpers (Vanilla JS - No jQuery dependency)
    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.style.zIndex = '1060';
        modal.classList.add('in');
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
        if (!document.getElementById(id + '-backdrop')) {
            var backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade in';
            backdrop.id = id + '-backdrop';
            backdrop.style.zIndex = '1050';
            backdrop.addEventListener('click', function() { closeModal(id); });
            document.body.appendChild(backdrop);
        }
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('in');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        var backdrop = document.getElementById(id + '-backdrop');
        if (backdrop) backdrop.remove();
    }

    document.addEventListener('click', function(e) {
        var dismiss = e.target.closest('[data-dismiss="modal"]');
        if (dismiss) {
            var m = dismiss.closest('.modal');
            if (m) closeModal(m.id);
        }
        var toggle = e.target.closest('[data-toggle="modal"]');
        if (toggle) {
            var target = toggle.getAttribute('data-target');
            if (target && target.startsWith('#')) {
                e.preventDefault();
                openModal(target.substring(1));
            }
        }
    });

    // 3. STORY POINTS EDITING (PLANNING POKER)
    var currentEditingTaskId = null;
    var currentEditingPill = null;

    document.addEventListener('click', function(e) {
        var pill = e.target.closest('.tp-story-point-pill');
        if (pill) {
            var taskItem = pill.closest('.tp-task-item');
            currentEditingTaskId = taskItem ? taskItem.dataset.taskId : null;
            currentEditingPill = pill;
            var currentPoints = pill.dataset.points || '';

            document.getElementById('custom-story-points-input').value = currentPoints;

            // Highlight selected fibonacci button
            document.querySelectorAll('.btn-fibonacci').forEach(function(b) {
                b.classList.toggle('btn-primary', b.dataset.points === currentPoints);
                b.classList.toggle('btn-default', b.dataset.points !== currentPoints);
            });

            openModal('storyPointsModal');
        }
    });

    document.querySelectorAll('.btn-fibonacci').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var pts = this.dataset.points;
            document.getElementById('custom-story-points-input').value = pts;
            document.querySelectorAll('.btn-fibonacci').forEach(function(b) {
                b.classList.remove('btn-primary');
                b.classList.add('btn-default');
            });
            this.classList.remove('btn-default');
            this.classList.add('btn-primary');
        });
    });

    document.getElementById('btn-save-story-points')?.addEventListener('click', function() {
        if (!currentEditingTaskId) return;

        var pointsVal = document.getElementById('custom-story-points-input').value;

        fetch('/tasks/' + currentEditingTaskId + '/story-points', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ story_points: pointsVal ? parseFloat(pointsVal) : null })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && currentEditingPill) {
                currentEditingPill.dataset.points = data.story_points || '';
                currentEditingPill.innerHTML = '<i class="fa fa-calculator"></i> ' + (data.story_points !== null ? data.story_points + ' SP' : '+ SP');
                currentEditingPill.classList.toggle('points-empty', data.story_points === null);
                closeModal('storyPointsModal');
            }
        })
        .catch(function(err) {
            console.error('Error saving SP:', err);
        });
    });

    // 4. REALTIME SEARCH & FILTER
    var searchInput = document.getElementById('backlog-search-input');
    var filterType = document.getElementById('filter-issue-type');
    var filterPriority = document.getElementById('filter-priority');

    function filterTasks() {
        var query = (searchInput?.value || '').toLowerCase().trim();
        var typeVal = filterType?.value || '';
        var priorityVal = (filterPriority?.value || '').toLowerCase();

        document.querySelectorAll('.tp-task-item').forEach(function(item) {
            var name = (item.dataset.taskName || '').toLowerCase();
            var key = (item.dataset.taskKey || '').toLowerCase();
            var type = (item.dataset.issueType || '').toLowerCase();
            var priority = (item.dataset.priority || '').toLowerCase();

            var matchesQuery = !query || name.indexOf(query) !== -1 || key.indexOf(query) !== -1;
            var matchesType = !typeVal || type === typeVal;
            var matchesPriority = !priorityVal || priority.indexOf(priorityVal) !== -1;

            if (matchesQuery && matchesType && matchesPriority) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    searchInput?.addEventListener('input', filterTasks);
    filterType?.addEventListener('change', filterTasks);
    filterPriority?.addEventListener('change', filterTasks);

    // Initial drag-and-drop binding
    initDragAndDrop();
})();
</script>
@endpush
@endsection
