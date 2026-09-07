@php
    $mode = $mode ?? 'select';
    $compact = $compact ?? false;
    $select = $select ?? '#assignee_id';
    $aiTask = $task ?? null;
@endphp
<button
    type="button"
    class="ai-suggest-btn {{ $compact ? 'ai-suggest-btn-compact' : '' }}"
    data-ai-assignee
    data-ai-mode="{{ $mode }}"
    data-ai-select="{{ $select }}"
    @if($aiTask)
        data-task-id="{{ $aiTask->id }}"
        data-name="{{ e($aiTask->name) }}"
        data-description="{{ e(\Illuminate\Support\Str::limit(strip_tags($aiTask->description ?? ''), 800)) }}"
        data-project-id="{{ $aiTask->project_id }}"
        data-issue-type="{{ $aiTask->issue_type ?? 'task' }}"
        data-priority="{{ $aiTask->priority ?? 'medium' }}"
    @endif
    title="Dùng AI đề xuất người xử lý phù hợp"
>
    <i class="fa fa-magic" style="color: #8b5cf6;"></i>
    <span>{{ $compact ? 'AI' : '✨ AI Đề xuất' }}</span>
</button>
