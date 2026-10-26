@php
    $currentRoute = request()->route()?->getName() ?? '';
    $currentTab = $active ?? match(true) {
        str_starts_with($currentRoute, 'sprints.') => 'sprints',
        $currentRoute === 'projects.board' => 'board',
        $currentRoute === 'projects.show' => 'overview',
        str_starts_with($currentRoute, 'projects.members') => 'members',
        str_starts_with($currentRoute, 'projects.roles') || str_starts_with($currentRoute, 'projects.permissions') => 'roles',
        $currentRoute === 'projects.timeline' => 'timeline',
        $currentRoute === 'projects.roadmap' => 'roadmap',
        str_starts_with($currentRoute, 'projects.reports') => 'reports',
        str_starts_with($currentRoute, 'meetings.') => 'meetings',
        default => '',
    };
@endphp

<nav class="pd-project-nav mb-4" aria-label="Điều hướng dự án">
    <a href="{{ route('projects.index') }}" class="pd-nav-item" title="Quay lại danh sách dự án">
        <i class="fa fa-folder-open-o"></i>
        <span>Dự án</span>
    </a>
    <a href="{{ route('projects.show', $project) }}" class="pd-nav-item {{ $currentTab === 'overview' ? 'active' : '' }}">
        <i class="fa fa-th-large"></i>
        <span>Tổng quan</span>
    </a>
    <a href="{{ route('projects.board', $project) }}" class="pd-nav-item {{ $currentTab === 'board' ? 'active' : '' }}">
        <i class="fa fa-columns"></i>
        <span>Bảng (Board)</span>
    </a>
    <a href="{{ route('sprints.index', $project) }}" class="pd-nav-item {{ $currentTab === 'sprints' ? 'active' : '' }}">
        <i class="fa fa-list-alt"></i>
        <span>Backlog / Sprint</span>
    </a>
    <a href="{{ route('projects.members.index', $project) }}" class="pd-nav-item {{ $currentTab === 'members' ? 'active' : '' }}">
        <i class="fa fa-users"></i>
        <span>Thành viên</span>
    </a>
    <a href="{{ route('projects.roles.index', $project) }}" class="pd-nav-item {{ $currentTab === 'roles' ? 'active' : '' }}">
        <i class="fa fa-shield"></i>
        <span>Vai trò &amp; Quyền</span>
    </a>
    <a href="{{ route('projects.timeline', $project) }}" class="pd-nav-item {{ $currentTab === 'timeline' ? 'active' : '' }}">
        <i class="fa fa-calendar"></i>
        <span>Timeline</span>
    </a>
    <a href="{{ route('projects.roadmap', $project) }}" class="pd-nav-item {{ $currentTab === 'roadmap' ? 'active' : '' }}">
        <i class="fa fa-calendar-check-o"></i>
        <span>Lịch biểu</span>
    </a>
    @if(\Illuminate\Support\Facades\Route::has('projects.reports.show'))
    <a href="{{ route('projects.reports.show', $project) }}" class="pd-nav-item {{ $currentTab === 'reports' ? 'active' : '' }}">
        <i class="fa fa-bar-chart"></i>
        <span>Báo cáo</span>
    </a>
    @endif
    <a href="{{ route('meetings.index', $project) }}" class="pd-nav-item {{ $currentTab === 'meetings' ? 'active' : '' }}">
        <i class="fa fa-video-camera"></i>
        <span>Phòng họp</span>
    </a>
</nav>
