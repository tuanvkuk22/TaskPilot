@extends('layouts.app')

@push('styles')
<style>
body {
    background: #eef2f7;
}
.dashboard-shell {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    gap: 24px;
    padding: 28px 0 40px;
}
.dashboard-sidebar {
    background: #fff;
    border-radius: 28px;
    box-shadow: 0 24px 60px rgba(34, 60, 80, 0.08);
    padding: 28px;
    display: flex;
    flex-direction: column;
    min-height: 520px;
}
.sidebar-profile {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: 32px;
}
.sidebar-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 18px;
    border: 4px solid #eef2f7;
}
.sidebar-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.sidebar-profile h2 {
    margin: 0 0 8px;
    font-size: 22px;
    color: #1f304f;
}
.sidebar-profile p {
    margin: 0;
    color: #6a7a93;
    font-size: 14px;
}
.sidebar-card {
    background: #f8fbff;
    border-radius: 20px;
    padding: 20px 22px;
    margin-bottom: 24px;
}
.sidebar-card strong {
    display: block;
    color: #30406c;
    margin-bottom: 10px;
    font-size: 13px;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.sidebar-card span {
    font-size: 28px;
    font-weight: 700;
    color: #1f304f;
}
.sidebar-nav {
    display: grid;
    gap: 12px;
}
.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border-radius: 16px;
    text-decoration: none;
    color: #2b3d5d;
    background: #fff;
    border: 1px solid #e6ecf5;
    transition: all .2s ease;
}
.sidebar-nav a:hover,
.sidebar-nav a.active {
    background: #f0f5ff;
    border-color: #d3e0ff;
}
.sidebar-nav a i {
    width: 24px;
    text-align: center;
}
.dashboard-main {
    display: flex;
    flex-direction: column;
    gap: 24px;
}
.dashboard-panel {
    background: #fff;
    border-radius: 28px;
    box-shadow: 0 24px 60px rgba(34, 60, 80, 0.08);
    padding: 28px;
}
.dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 18px;
    margin-bottom: 24px;
}
.dashboard-header h1 {
    margin: 0;
    font-size: 32px;
    color: #1f304f;
}
.dashboard-header p {
    margin: 6px 0 0;
    color: #6a7a93;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-radius: 999px;
    border: 1px solid #d8e1f5;
    font-weight: 600;
    color: #3f4f76;
    background: #f6f9ff;
}
.status-pill span {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    background: #27ae60;
}
.status-pill.status-inactive span { background: #f39c12; }
.status-pill.status-banned span { background: #e74c3c; }
.overview-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}
.overview-card {
    background: #fafbff;
    border: 1px solid #e6ecf5;
    border-radius: 20px;
    padding: 24px;
    min-height: 140px;
}
.overview-card h3 {
    margin: 0 0 16px;
    font-size: 15px;
    color: #6a7a93;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.overview-card strong {
    display: block;
    font-size: 34px;
    color: #1f304f;
}
.overview-card p {
    margin: 14px 0 0;
    color: #6a7a93;
}
.dashboard-content {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 24px;
}
.info-panel {
    display: grid;
    gap: 18px;
}
.info-card {
    background: #fff;
    border: 1px solid #e6ecf5;
    border-radius: 20px;
    padding: 24px;
}
.info-card h3 {
    margin: 0 0 16px;
    font-size: 18px;
    color: #1f304f;
}
.info-card .info-grid {
    display: grid;
    gap: 16px;
}
.info-item {
    display: grid;
    gap: 6px;
}
.info-item strong {
    color: #5f6e8a;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.info-item span {
    font-size: 16px;
    color: #2b3d5d;
}
.quick-actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}
.quick-actions a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 16px 14px;
    border-radius: 16px;
    border: 1px solid #e6ecf5;
    text-decoration: none;
    color: #2b3d5d;
    background: #f8fbff;
    font-weight: 600;
}
.quick-actions a:hover {
    background: #eef4ff;
}
@media (max-width: 1024px) {
    .dashboard-shell {
        grid-template-columns: 1fr;
    }
    .overview-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .dashboard-content {
        grid-template-columns: 1fr;
    }
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 680px) {
    .overview-grid {
        grid-template-columns: 1fr;
    }
    .sidebar-card,
    .sidebar-nav a,
    .info-card,
    .overview-card {
        padding: 20px;
    }
}
</style>
@endpush

@section('content')
<div class="dashboard-shell">
    <aside class="dashboard-sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar">
                <img src="{{ Auth::user()->avatar ?? 'https://via.placeholder.com/200?text=Avatar' }}" alt="Avatar">
            </div>
            <h2>{{ Auth::user()->full_name ?? Auth::user()->name }}</h2>
            <p>{{ Auth::user()->job_title ?? 'Thành viên dự án' }}</p>
        </div>

        <div class="sidebar-card">
            <strong>Thông tin nhanh</strong>
            <span>{{ Auth::user()->username ?? '-' }}</span>
            <p style="margin: 12px 0 0; color: #6a7a93;">{{ Auth::user()->department ?? 'Chưa có phòng ban' }}</p>
        </div>

        <div class="sidebar-card">
            <strong>Trạng thái hồ sơ</strong>
            <span class="status-pill status-{{ Auth::user()->status }}"> 
                <span></span> {{ ucfirst(Auth::user()->status) }}
            </span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('home') }}" class="active"><i class="fa fa-tachometer"></i> Tổng quan</a>
            <a href="{{ route('projects.index') }}"><i class="fa fa-briefcase"></i> Dự án</a>
            <a href="{{ route('tasks.index') }}"><i class="fa fa-tasks"></i> Công việc</a>
            <a href="{{ route('companies.index') }}"><i class="fa fa-building"></i> Công ty</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-power-off"></i> Đăng xuất</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">{{ csrf_field() }}</form>
        </nav>
    </aside>

    <main class="dashboard-main">
        <section class="dashboard-panel">
            <div class="dashboard-header">
                <div>
                    <h1>Chào mừng, {{ Auth::user()->full_name ?? Auth::user()->name }}</h1>
                    <p>Tổng quan cập nhật nhanh các dự án và công việc hiện tại.</p>
                </div>
                <div class="status-pill status-{{ Auth::user()->status }}">
                    <span></span> Trạng thái: {{ ucfirst(Auth::user()->status) }}
                </div>
            </div>

            <div class="overview-grid">
                <article class="overview-card">
                    <h3>Tất cả dự án</h3>
                    <strong>{{ $totalProjects }}</strong>
                    <p>Số dự án đang quản lý trên hệ thống.</p>
                </article>
                <article class="overview-card">
                    <h3>Tất cả công việc</h3>
                    <strong>{{ $totalTasks }}</strong>
                    <p>Tổng số nhiệm vụ đang lưu trữ.</p>
                </article>
                <article class="overview-card">
                    <h3>Công ty</h3>
                    <strong>{{ $totalCompanies }}</strong>
                    <p>Số công ty đang hoạt động trong hệ thống.</p>
                </article>
                <article class="overview-card">
                    <h3>Công việc của bạn</h3>
                    <strong>{{ $userTasks }}</strong>
                    <p>Số nhiệm vụ bạn đang phụ trách.</p>
                </article>
            </div>
        </section>

        <section class="dashboard-panel dashboard-content">
            <div class="info-panel">
                <div class="info-card">
                    <h3>Thông tin tài khoản</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Email</strong>
                            <span>{{ Auth::user()->email }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Múi giờ</strong>
                            <span>{{ Auth::user()->timezone ?? config('app.timezone') }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Lần đăng nhập cuối</strong>
                                <span>
                                    @if (Auth::user()->last_login_at)
                                        {{ Auth::user()->last_login_at->setTimezone(Auth::user()->timezone ?? config('app.timezone'))->format('d M Y H:i') }}
                                    @else
                                        Chưa từng đăng nhập
                                    @endif
                                </span>
                        </div>
                        <div class="info-item">
                            <strong>Tham gia từ</strong>
                            <span>{{ Auth::user()->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h3>Hành động nhanh</h3>
                    <div class="quick-actions">
                        <a href="{{ route('projects.index') }}">Dự án</a>
                        <a href="{{ route('tasks.index') }}">Công việc</a>
                        <a href="{{ route('companies.index') }}">Công ty</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
