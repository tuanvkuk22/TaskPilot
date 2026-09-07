@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/edit-shared.css') }}?v={{ file_exists(public_path('build/assets/edit-shared.css')) ? filemtime(public_path('build/assets/edit-shared.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('build/assets/task-shared.css') }}?v={{ file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time() }}" rel="stylesheet">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="fa fa-edit text-white"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold h5">Chỉnh sửa công việc</h4>
                            <p class="text-muted mb-0 small">Cập nhật thông tin cho công việc #{{ $task->issue_key ?? $task->id }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tasks.update', $task->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="mb-4">
                                    <label for="name" class="form-label fw-semibold">Tên công việc <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" id="name" name="name" required placeholder="Nhập tên công việc..." value="{{ old('name', $task->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label fw-semibold">Mô tả công việc</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" placeholder="Mô tả chi tiết về nhiệm vụ cần thực hiện...">{{ old('description', $task->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="card border mb-4">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 fw-semibold small text-uppercase text-muted">Phân loại & Theo dõi</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="issue_type" class="form-label fw-medium small">Loại issue</label>
                                                <select name="issue_type" id="issue_type" class="form-select">
                                                    <option value="task" {{ $task->issue_type === 'task' ? 'selected' : '' }}>Task</option>
                                                    <option value="bug" {{ $task->issue_type === 'bug' ? 'selected' : '' }}>Bug</option>
                                                    <option value="story" {{ $task->issue_type === 'story' ? 'selected' : '' }}>Story</option>
                                                    <option value="epic" {{ $task->issue_type === 'epic' ? 'selected' : '' }}>Epic</option>
                                                    <option value="subtask" {{ $task->issue_type === 'subtask' ? 'selected' : '' }}>Sub-task</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="parent_id" class="form-label fw-medium small">Epic cha</label>
                                                <select name="parent_id" id="parent_id" class="form-select">
                                                    <option value="">Không thuộc Epic</option>
                                                    @foreach($epics as $epic)
                                                        <option value="{{ $epic->id }}" {{ $task->parent_id == $epic->id ? 'selected' : '' }}>{{ $epic->issue_key ?? 'EPIC-' . $epic->id }} - {{ $epic->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border mb-4">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 fw-semibold small text-uppercase text-muted">Trạng thái & Độ ưu tiên</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="status" class="form-label fw-medium small">Trạng thái</label>
                                                <select name="status" id="status" class="form-select">
                                                    <option value="todo" {{ $task->status == 'todo' ? 'selected' : '' }}>Cần làm (Todo)</option>
                                                    <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>Đang làm (In Progress)</option>
                                                    <option value="done" {{ $task->status == 'done' ? 'selected' : '' }}>Hoàn thành (Done)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="priority" class="form-label fw-medium small">Độ ưu tiên</label>
                                                <select name="priority" id="priority" class="form-select">
                                                    <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Thấp</option>
                                                    <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Trung bình</option>
                                                    <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>Cao</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="hours" class="form-label fw-medium small">Số giờ dự kiến</label>
                                                <input type="number" class="form-control" id="hours" name="hours" min="0" value="{{ old('hours', $task->hours ?? 0) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card border mb-4">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 fw-semibold small text-uppercase text-muted">Dự án</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="project_id" class="form-label fw-medium small">Dự án <span class="text-danger">*</span></label>
                                            <select name="project_id" id="project_id" class="form-select" required>
                                                @foreach($projects as $project)
                                                    <option value="{{ $project->id }}" {{ $task->project_id == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border mb-4">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 fw-semibold small text-uppercase text-muted">Người phụ trách & Người báo cáo</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="ai-inline-actions" style="justify-content: space-between; width: 100%; margin-bottom: 6px;">
                                                <label for="assignee_id" class="form-label fw-medium small mb-0">Người phụ trách</label>
                                                @include('partials.ai_assignee_button', ['task' => $task, 'mode' => 'select', 'select' => '#assignee_id'])
                                            </div>
                                            <select name="assignee_id" id="assignee_id" class="form-select">
                                                <option value="">Chưa phân công</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ $task->assignee_id == $user->id ? 'selected' : '' }}>{{ $user->full_name ?? $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reporter_id" class="form-label fw-medium small">Người báo cáo</label>
                                            <select name="reporter_id" id="reporter_id" class="form-select">
                                                <option value="">Chưa chọn</option>
                                                @foreach($reporters as $reporter)
                                                    <option value="{{ $reporter->id }}" {{ $task->reporter_id == $reporter->id ? 'selected' : '' }}>{{ $reporter->full_name ?? $reporter->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border mb-4">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 fw-semibold small text-uppercase text-muted">Sprint & Kế hoạch</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="sprint" class="form-label fw-medium small">Sprint</label>
                                            <input type="text" name="sprint" id="sprint" class="form-control" placeholder="Sprint 1" value="{{ old('sprint', $task->sprint) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="sprint_id" class="form-label fw-medium small">Sprint</label>
                                            <select name="sprint_id" id="sprint_id" class="form-select">
                                                <option value="">Backlog</option>
                                                @foreach($sprints as $sprint)
                                                    <option value="{{ $sprint->id }}" {{ $task->sprint_id == $sprint->id ? 'selected' : '' }}>{{ $sprint->name }} ({{ $sprint->status }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border mb-4">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 fw-semibold small text-uppercase text-muted">Lên kế hoạch</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="due_date" class="form-label fw-medium small">Hạn hoàn thành</label>
                                            <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="story_points" class="form-label fw-medium small">Story points</label>
                                            <input type="number" min="0" step="0.5" name="story_points" id="story_points" class="form-control" value="{{ old('story_points', $task->story_points) }}">
                                        </div>
                                        <div class="mb-0">
                                            <label for="labels" class="form-label fw-medium small">Labels</label>
                                            <input type="text" name="labels" id="labels" class="form-control" placeholder="frontend, urgent" value="{{ old('labels', $task->labels) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-warning px-4">
                                <i class="fa fa-save me-1"></i> Cập nhật công việc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.ai_assignee_assets')
@endsection
