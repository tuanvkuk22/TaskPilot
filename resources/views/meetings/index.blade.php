@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/project-dashboard-luxury.css') }}?v={{ file_exists(public_path('css/project-dashboard-luxury.css')) ? filemtime(public_path('css/project-dashboard-luxury.css')) : time() }}" rel="stylesheet">
<link href="{{ asset('css/meeting-ui.css') }}?v={{ file_exists(public_path('css/meeting-ui.css')) ? filemtime(public_path('css/meeting-ui.css')) : time() }}" rel="stylesheet">
@endpush

@section('content')
<div class="col-xs-12 col-md-12 p-0">
    <div class="tp-meeting-shell">
        <!-- Project Header -->
        <div class="pd-header mb-3" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div class="pd-header-left">
                <div class="pd-breadcrumb" style="margin-bottom: 6px; font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    <a href="{{ route('projects.index') }}" style="color: #64748b; text-decoration: none;">Dự án</a>
                    <span>/</span>
                    <a href="{{ route('projects.show', $project) }}" style="color: #64748b; text-decoration: none;">{{ $project->name }}</a>
                    <span>/</span>
                    <span style="color: #0f172a; font-weight: 700;">Phòng họp</span>
                </div>
                <div class="pd-title-row" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h1 class="pd-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a;">{{ $project->name }} / Phòng họp trực tuyến</h1>
                    <span class="pd-project-key">{{ $project->project_code ?? 'PROJ' }}</span>
                    <span class="pd-project-status">{{ strtoupper($project->status ?? 'active') }}</span>
                </div>
                <p class="text-muted" style="margin: 6px 0 0 0; color: #64748b; font-size: 13px;">
                    Không gian họp video call trực tuyến (LiveKit WebRTC), chat và ghi lại biên bản cuộc họp của dự án.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-primary tp-btn-primary" data-toggle="modal" data-target="#newMeeting" data-bs-toggle="modal" data-bs-target="#newMeeting" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <i class="fa fa-video-camera" style="color: #ffffff !important;"></i>
                    <span>Tạo phòng họp</span>
                </button>
            </div>
        </div>

        <!-- Unified Project Sub-Nav Bar -->
        @include('projects.partials.nav', ['project' => $project, 'active' => 'meetings'])

        <!-- Meetings List -->
        <div class="tp-meeting-list" style="margin-top: 20px;">
            @forelse($meetings as $meeting)
                <a class="tp-meeting-row" href="{{ route('meetings.show', $meeting) }}">
                    <span class="tp-meeting-icon">
                        <i class="fa fa-video-camera"></i>
                    </span>
                    <span style="flex: 1;">
                        <strong style="font-size: 15px; color: #0f172a;">{{ $meeting->title }}</strong>
                        <small style="display: block; color: #64748b; margin-top: 3px;">
                            {{ $meeting->starts_at ? $meeting->starts_at->format('d/m/Y H:i') : 'Chưa đặt giờ' }}
                            · {{ $meeting->members->count() }} thành viên
                            @if($meeting->description)
                                · <span class="text-muted">{{ Str::limit($meeting->description, 50) }}</span>
                            @endif
                        </small>
                    </span>
                    <span class="tp-status" style="font-weight: 600; text-transform: uppercase; font-size: 12px; margin-right: 12px;">
                        {{ $meeting->status ?? 'Sẵn sàng' }}
                    </span>
                    <i class="fa fa-chevron-right text-muted"></i>
                </a>
            @empty
                <div class="tp-empty-state" style="padding: 60px 20px; text-align: center;">
                    <i class="fa fa-comments-o" style="font-size: 40px; color: #2563eb; margin-bottom: 12px; display: block;"></i>
                    <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Chưa có phòng họp</h2>
                    <p style="color: #64748b; font-size: 13.5px; margin-bottom: 16px;">Tạo một phòng họp để bắt đầu trao đổi video và âm thanh cùng các thành viên trong nhóm.</p>
                    <button type="button" class="btn btn-primary tp-btn-primary" data-toggle="modal" data-target="#newMeeting" data-bs-toggle="modal" data-bs-target="#newMeeting" style="padding: 8px 16px; border-radius: 8px;">
                        <i class="fa fa-plus me-1"></i> Tạo phòng họp ngay
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Tạo Phòng Họp Mới -->
<div class="modal fade" id="newMeeting" tabindex="-1" role="dialog" aria-labelledby="newMeetingLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <form method="POST" action="{{ route('meetings.store', $project) }}">
                @csrf
                <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h4 class="modal-title" id="newMeetingLabel" style="font-weight: 700; font-size: 16px; margin: 0; color: #0f172a;">
                        <i class="fa fa-video-camera text-primary me-1"></i> Tạo phòng họp trực tuyến
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Đóng" style="font-size: 22px; border: none; background: transparent; cursor: pointer; color: #64748b; margin-top: -4px;">&times;</button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Tên cuộc họp <span class="text-danger">*</span></label>
                        <input name="title" required maxlength="255" class="form-control" placeholder="Ví dụ: Họp Sprint Review, Trao đổi tiến độ..." style="border-radius: 8px; height: 38px; font-size: 13.5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Mô tả / Mục tiêu cuộc họp</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Nội dung thảo luận hoặc tài liệu chuẩn bị..." style="border-radius: 8px; font-size: 13.5px; resize: vertical;"></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Thời gian bắt đầu</label>
                        <input name="starts_at" type="datetime-local" class="form-control" style="border-radius: 8px; height: 38px; font-size: 13.5px;">
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Hủy</button>
                    <button type="submit" class="btn btn-primary tp-btn-primary" style="border-radius: 8px; font-weight: 600;">
                        <i class="fa fa-check me-1"></i> Tạo và mở phòng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
