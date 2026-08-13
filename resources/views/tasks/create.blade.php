@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="mb-0 fw-bold h5">Tạo công việc mới</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tasks.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="project_id" class="form-label fw-medium">Dự án <span class="text-danger">*</span></label>
                            <select name="project_id" id="project_id" class="form-select" required>
                                <option value="">-- Chọn dự án --</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ (($project_id ?? '') == $project->id) ? 'selected' : '' }}>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium">Tên công việc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="Nhập tên công việc">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><label for="issue_type" class="form-label fw-medium">Loại issue</label><select name="issue_type" id="issue_type" class="form-select"><option value="task">Task</option><option value="bug">Bug</option><option value="story">Story</option><option value="epic">Epic</option><option value="subtask">Sub-task</option></select></div>
                            <div class="col-md-4"><label for="assignee_id" class="form-label fw-medium">Người phụ trách</label><select name="assignee_id" id="assignee_id" class="form-select"><option value="">Chưa phân công</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->full_name ?? $user->name }}</option>@endforeach</select></div>
                            <div class="col-md-4"><label for="sprint" class="form-label fw-medium">Sprint</label><input type="text" name="sprint" id="sprint" class="form-control" placeholder="Sprint 1"></div>
                        </div>

                        <div class="row g-3 mb-3"><div class="col-md-6"><label for="parent_id" class="form-label fw-medium">Epic cha</label><select name="parent_id" id="parent_id" class="form-select"><option value="">Không thuộc Epic</option>@foreach($epics as $epic)<option value="{{ $epic->id }}">{{ $epic->issue_key ?? 'EPIC-' . $epic->id }} - {{ $epic->name }}</option>@endforeach</select></div><div class="col-md-6"><label for="sprint_id" class="form-label fw-medium">Sprint</label><select name="sprint_id" id="sprint_id" class="form-select"><option value="">Backlog</option>@foreach($sprints as $sprint)<option value="{{ $sprint->id }}">{{ $sprint->name }} ({{ $sprint->status }})</option>@endforeach</select></div></div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-medium">Mô tả công việc</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Nhập mô tả về nhiệm vụ..."></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label fw-medium">Trạng thái</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="todo">Cần làm (Todo)</option>
                                    <option value="in_progress">Đang làm (In Progress)</option>
                                    <option value="done">Hoàn thành (Done)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="priority" class="form-label fw-medium">Độ ưu tiên</label>
                                <select name="priority" id="priority" class="form-select">
                                    <option value="low">Thấp</option>
                                    <option value="medium" selected>Trung bình</option>
                                    <option value="high">Cao</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="days" class="form-label fw-medium">Số ngày dự kiến</label>
                                <input type="number" class="form-control" id="days" name="days" min="0" value="1">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="hours" class="form-label fw-medium">Số giờ dự kiến</label>
                            <input type="number" class="form-control" id="hours" name="hours" min="0" value="0">
                        </div>

                        <div class="row g-3 mb-4"><div class="col-md-4"><label for="due_date" class="form-label fw-medium">Hạn hoàn thành</label><input type="date" name="due_date" id="due_date" class="form-control"></div><div class="col-md-4"><label for="story_points" class="form-label fw-medium">Story points</label><input type="number" min="0" step="0.5" name="story_points" id="story_points" class="form-control"></div><div class="col-md-4"><label for="labels" class="form-label fw-medium">Labels</label><input type="text" name="labels" id="labels" class="form-control" placeholder="frontend, urgent"></div></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Hủy</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa fa-save me-1"></i> Lưu công việc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection