@extends('layouts.app')

@section('content')
<section class="tp-page-head">
    <div><span class="tp-eyebrow">TASKPILOT WORKSPACE</span><h1>Chào {{ auth()->user()->name }}</h1><p>Tổng quan công việc và không gian cộng tác của bạn.</p></div>
    <div class="tp-actions"><a href="{{ route('tasks.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Tạo công việc</a><a href="{{ route('projects.create') }}" class="btn btn-default"><i class="fa fa-folder-open-o"></i> Dự án mới</a></div>
</section>
<section class="tp-stat-grid">
    <article class="tp-surface tp-stat"><i class="fa fa-folder-o"></i><span class="tp-stat-label">Dự án</span><strong>{{ $totalProjects }}</strong></article>
    <article class="tp-surface tp-stat"><i class="fa fa-check-square-o"></i><span class="tp-stat-label">Công việc</span><strong>{{ $totalTasks }}</strong></article>
    <article class="tp-surface tp-stat"><i class="fa fa-building-o"></i><span class="tp-stat-label">Khách hàng</span><strong>{{ $totalCompanies }}</strong></article>
    <article class="tp-surface tp-stat"><i class="fa fa-user-circle-o"></i><span class="tp-stat-label">Được giao cho bạn</span><strong>{{ $userTasks }}</strong></article>
</section>
<section class="row">
    <div class="col-md-8"><div class="tp-surface"><div class="tp-surface-header"><strong>Khởi động nhanh</strong></div><div class="tp-surface-body"><p class="text-muted">Quản lý công việc theo dự án, theo dõi tiến độ bằng Board và tập trung vào việc quan trọng nhất.</p><div class="tp-actions"><a class="btn btn-primary" href="{{ route('projects.index') }}">Mở danh sách dự án</a><a class="btn btn-default" href="{{ route('tasks.index') }}">Mở bảng công việc</a></div></div></div></div>
    <div class="col-md-4"><div class="tp-surface"><div class="tp-surface-header"><strong>Gợi ý hôm nay</strong></div><div class="tp-surface-body"><p class="text-muted">Tạo một sprint từ dự án để gom backlog và theo dõi mục tiêu phát hành.</p><a href="{{ route('projects.index') }}" class="btn btn-link">Xem dự án <i class="fa fa-arrow-right"></i></a></div></div></div>
</section>
@endsection
