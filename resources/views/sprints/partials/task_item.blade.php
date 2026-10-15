@php
    $type = strtolower($task->issue_type ?? 'task');
    $typeIcon = match($type) {
        'story' => 'bookmark',
        'bug' => 'bug',
        'epic' => 'bolt',
        default => 'check-square-o',
    };
    $priorityClass = match(strtolower($task->priority ?? 'medium')) {
        'khẩn cấp', 'urgent' => 'tp-priority-urgent',
        'cao', 'high' => 'tp-priority-cao',
        'thấp', 'low' => 'tp-priority-thap',
        default => 'tp-priority-trungbinh',
    };
    $statusClass = 'tp-status-' . strtolower(str_replace(' ', '_', $task->status ?? 'todo'));
    $issueKey = $task->issue_key ?: 'TP-' . $task->id;
@endphp

<article class="tp-task-item" 
         draggable="true" 
         data-task-id="{{ $task->id }}"
         data-task-name="{{ $task->name }}"
         data-task-key="{{ $issueKey }}"
         data-issue-type="{{ $type }}"
         data-priority="{{ $task->priority ?? 'Trung bình' }}"
         id="task-row-{{ $task->id }}">
    
    <div class="tp-task-item-left">
        <span class="tp-drag-handle" title="Kéo thả để di chuyển">
            <i class="fa fa-ellipsis-v"></i><i class="fa fa-ellipsis-v" style="margin-left: -2px;"></i>
        </span>

        <span class="tp-issue-type-badge type-{{ $type }}" title="{{ ucfirst($type) }}">
            <i class="fa fa-{{ $typeIcon }}"></i>
        </span>

        <span class="tp-task-key">{{ $issueKey }}</span>

        <a href="{{ route('tasks.show', $task) }}" class="tp-task-name" title="{{ $task->name }}">
            {{ $task->name }}
        </a>
    </div>

    <div class="tp-task-item-right">
        <!-- Story Points (Planning Poker Estimation) -->
        <span class="tp-story-point-pill {{ is_null($task->story_points) ? 'points-empty' : '' }}" 
              data-points="{{ $task->story_points }}"
              title="Nhấn để ước lượng Story Points">
            <i class="fa fa-calculator"></i>
            {{ is_null($task->story_points) ? '+ SP' : $task->story_points . ' SP' }}
        </span>

        <!-- Priority Pill -->
        <span class="tp-badge-priority {{ $priorityClass }}">
            {{ $task->priority ?: 'Trung bình' }}
        </span>

        <!-- Status Badge -->
        <span class="tp-badge-status {{ $statusClass }}">
            {{ ucfirst(str_replace('_', ' ', $task->status ?: 'Todo')) }}
        </span>

        <!-- Assignee Avatar -->
        @if($task->assignee)
            <div class="tp-assignee-chip" title="Người phụ trách: {{ $task->assignee->name }}">
                <span class="tp-assignee-avatar">
                    {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                </span>
                <span class="hidden-xs">{{ $task->assignee->name }}</span>
            </div>
        @else
            <span class="tp-unassigned-chip" title="Chưa phân công">
                <i class="fa fa-user-o me-1"></i> Chưa gán
            </span>
        @endif

        <!-- Move Sprint Selector -->
        <select class="tp-move-sprint-select" title="Chuyển vào Sprint khác hoặc trả về Backlog">
            <option value="" {{ empty($currentSprintId) ? 'selected' : '' }}>Vào Backlog</option>
            @foreach($movableSprints as $ms)
                <option value="{{ $ms->id }}" {{ $currentSprintId == $ms->id ? 'selected' : '' }}>
                    {{ $ms->name }} ({{ $ms->status === 'active' ? 'Đang chạy' : 'Kế hoạch' }})
                </option>
            @endforeach
        </select>
    </div>
</article>
