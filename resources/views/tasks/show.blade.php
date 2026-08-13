@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h3 class="h4 mb-0 fw-bold text-dark">{{ $task->name }}</h3>
                    <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }} px-3 py-2">
                        {{ ucfirst($task->priority) }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Dự án</span>
                            <span class="fw-semibold text-primary"><i class="fa fa-folder-o me-1"></i>{{ $task->project->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Trạng thái</span>
                            <span class="badge bg-secondary-subtle text-dark border px-2 py-1">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Người phụ trách</span>
                            <span class="fw-medium"><i class="fa fa-user-circle-o me-1"></i>{{ $task->assignee->full_name ?? $task->assignee->name ?? 'Chưa phân công' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Thời gian ước tính</span>
                            <span class="fw-medium">{{ $task->days ?? 0 }} ngày, {{ $task->hours ?? 0 }} giờ</span>
                        </div>
                        <div class="col-md-6"><span class="text-muted small d-block">Loại issue</span><span class="fw-medium">{{ ucfirst($task->issue_type ?? 'task') }}</span></div>
                        <div class="col-md-6"><span class="text-muted small d-block">Hạn hoàn thành</span><span class="fw-medium">{{ optional($task->due_date)->format('d/m/Y') ?? 'Chưa đặt' }}</span></div>
                        <div class="col-md-6"><span class="text-muted small d-block">Sprint / Story points</span><span class="fw-medium">{{ $task->sprint ?: 'Backlog' }} / {{ $task->story_points ?? '—' }}</span></div>
                        <div class="col-md-6"><span class="text-muted small d-block">Labels</span><span class="fw-medium">{{ $task->labels ?: 'Chưa gắn nhãn' }}</span></div>
                    </div>

                    <hr>

                    <h5 class="fw-bold mb-2">Mô tả công việc</h5>
                    <p class="text-secondary mb-4">{{ $task->description ?: 'Chưa có mô tả cho công việc này.' }}</p>

                    <div class="task-worklog-panel mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold mb-0">Time tracking</h5><strong>{{ number_format($worklogs->sum('hours'), 2) }} giờ đã ghi</strong></div>
                        <form method="POST" action="{{ route('tasks.worklog', $task) }}" class="row g-2 mb-3">@csrf<div class="col-md-3"><input type="number" name="hours" class="form-control form-control-sm" min="0.1" max="24" step="0.25" placeholder="Số giờ" required></div><div class="col-md-3"><input type="date" name="logged_at" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required></div><div class="col-md-4"><input type="text" name="description" class="form-control form-control-sm" placeholder="Bạn đã làm gì?"></div><div class="col-md-2"><button class="btn btn-primary btn-sm w-100">Ghi giờ</button></div></form>
                        @forelse($worklogs as $worklog)<div class="task-worklog-row"><span>{{ $worklog->logged_at->format('d/m/Y') }}</span><strong>{{ number_format($worklog->hours, 2) }}h</strong><span>{{ $worklog->description ?: 'Không có mô tả' }}</span><small>{{ $worklog->user->name ?? 'User' }}</small></div>@empty<p class="text-muted small mb-0">Chưa có worklog.</p>@endforelse
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Quay lại
                        </a>

                        <div class="d-flex gap-2">
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary">
                                <i class="fa fa-pencil me-1"></i> Chỉnh sửa
                            </a>
                            <form method="POST" action="{{ route('tasks.destroy', $task->id) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa công việc này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-trash me-1"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection