<!-- Professional Jira/Trello Right Sidebar Component -->
<aside class="tp-right-sidebar">
<div class="card border-0 shadow-sm mb-4 tp-sidebar-card tp-activity-card">
    <div class="card-header bg-white border-bottom py-3 fw-bold text-dark d-flex justify-content-between align-items-center">
        <span><i class="fa fa-history text-primary me-2"></i>Hoạt động gần đây</span>
        <span class="badge bg-primary bg-opacity-10 text-primary">Live</span>
    </div>
    <div class="card-body p-3">
        <ul class="list-unstyled mb-0 tp-activity-list">
            <li class="d-flex align-items-start gap-2 mb-3 pb-2 border-bottom tp-activity-item">
                <span class="badge rounded-circle bg-primary-subtle text-primary p-2 mt-1 tp-activity-icon"><i class="fa fa-plus"></i></span>
                <div class="tp-activity-copy">
                    <div class="small fw-semibold text-dark">Dự án mới vừa khởi tạo</div>
                    <small class="text-muted d-block">Hệ thống ghi nhận dự án gần nhất</small>
                    <small class="text-secondary fs-7">10 phút trước</small>
                </div>
            </li>
            <li class="d-flex align-items-start gap-2 mb-3 pb-2 border-bottom tp-activity-item">
                <span class="badge rounded-circle bg-success-subtle text-success p-2 mt-1 tp-activity-icon"><i class="fa fa-check"></i></span>
                <div class="tp-activity-copy">
                    <div class="small fw-semibold text-dark">Nhiệm vụ hoàn thành</div>
                    <small class="text-muted d-block">Đã cập nhật tiến độ công việc</small>
                    <small class="text-secondary fs-7">1 giờ trước</small>
                </div>
            </li>
            <li class="d-flex align-items-start gap-2 tp-activity-item">
                <span class="badge rounded-circle bg-info-subtle text-info p-2 mt-1 tp-activity-icon"><i class="fa fa-user-plus"></i></span>
                <div class="tp-activity-copy">
                    <div class="small fw-semibold text-dark">Lời mời thành viên</div>
                    <small class="text-muted d-block">Gửi email mời đồng nghiệp</small>
                    <small class="text-secondary fs-7">2 giờ trước</small>
                </div>
            </li>
        </ul>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 tp-sidebar-card tp-shortcuts-card">
    <div class="card-header bg-white border-bottom py-3 fw-bold text-dark">
        <i class="fa fa-bolt text-warning me-2"></i>Lối tắt công việc
    </div>
    <div class="card-body p-3">
        <div class="d-grid gap-2">
            <a href="{{ route('projects.create') }}" class="btn btn-sm btn-outline-primary text-start">
                <i class="fa fa-plus-circle me-2"></i> Tạo dự án mới
            </a>
            <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-outline-success text-start">
                <i class="fa fa-tasks me-2"></i> Thêm công việc (Kanban)
            </a>
            <a href="{{ route('teams.create') }}" class="btn btn-sm btn-outline-info text-start">
                <i class="fa fa-users me-2"></i> Tạo đội nhóm kiểu Jira
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm bg-primary text-white tp-sidebar-card tp-tip-card">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-2"><i class="fa fa-lightbulb-o me-2"></i>Mẹo Jira / Trello</h6>
        <p class="small text-white text-opacity-85 mb-0">Khi mời thành viên, nhập username hoặc email và gõ Enter / phẩy để tự động tạo nhãn pill khoanh tròn chuyên nghiệp.</p>
    </div>
</div>
</aside>
