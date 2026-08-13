@extends('layouts.app')
@section('content')
<div class="tp-meeting-shell">
    <div class="tp-meeting-heading"><div><span class="tp-kicker">{{ $project->project_code }}</span><h1>Phòng họp trực tuyến</h1><p>Không gian họp, chat và tổng hợp công việc của {{ $project->name }}.</p></div><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMeeting"><i class="fa fa-plus"></i> Tạo phòng họp</button></div>
    <div class="tp-meeting-list">
        @forelse($meetings as $meeting)
            <a class="tp-meeting-row" href="{{ route('meetings.show', $meeting) }}"><span class="tp-meeting-icon"><i class="fa fa-video-camera"></i></span><span><strong>{{ $meeting->title }}</strong><small>{{ $meeting->starts_at?->format('d/m/Y H:i') }} · {{ $meeting->members->count() }} thành viên</small></span><span class="tp-status">{{ $meeting->status }}</span><i class="fa fa-chevron-right"></i></a>
        @empty
            <div class="tp-empty-state"><i class="fa fa-comments-o"></i><h2>Chưa có phòng họp</h2><p>Tạo một phòng để bắt đầu trao đổi cùng nhóm.</p></div>
        @endforelse
    </div>
</div>
<div class="modal fade" id="newMeeting" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('meetings.store', $project) }}" class="tp-meeting-form">@csrf<h3>Tạo phòng họp</h3><label>Tên cuộc họp<input name="title" required maxlength="255" class="form-control"></label><label>Mô tả<textarea name="description" class="form-control" rows="3"></textarea></label><label>Thời gian<input name="starts_at" type="datetime-local" class="form-control"></label><button class="btn btn-primary">Tạo và mở phòng</button></form></div></div></div>
@endsection
