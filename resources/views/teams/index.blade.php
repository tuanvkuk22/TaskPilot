@extends('layouts.app')

@section('content')
<section class="tp-page team-page">
    <div class="tp-page-shell">
        <header class="tp-page-head">
            <div><span class="tp-eyebrow">WORKSPACE</span><h1>Đội nhóm</h1><p>Quản lý đội nhóm và thành viên trong hệ thống.</p></div>
            <a href="{{ route('teams.create') }}" class="tp-btn tp-btn--primary"><i class="fa fa-plus"></i> Tạo đội nhóm</a>
        </header>
        <div class="team-grid">
            <div>
                <div class="tp-stat-grid">
                    <article class="tp-stat"><span class="tp-stat__icon"><i class="fa fa-users"></i></span><div><span class="tp-stat__label">Tổng đội nhóm</span><strong class="tp-stat__value">{{ $teams->count() }}</strong></div></article>
                    <article class="tp-stat tp-stat--success"><span class="tp-stat__icon"><i class="fa fa-user"></i></span><div><span class="tp-stat__label">Tổng thành viên</span><strong class="tp-stat__value">{{ $teams->sum('users_count') }}</strong></div></article>
                    <article class="tp-stat tp-stat--warning"><span class="tp-stat__icon"><i class="fa fa-user-plus"></i></span><div><span class="tp-stat__label">Đội nhóm đang tham gia</span><strong class="tp-stat__value">{{ $teams->count() }}</strong></div></article>
                </div>
                <section class="tp-card team-list-card">
                    <div class="tp-card__header"><div><strong>Danh sách đội nhóm</strong><span class="tp-meta">Các đội nhóm bạn đang tham gia</span></div><span class="tp-badge tp-badge--primary">{{ $teams->count() }} đội nhóm</span></div>
                    <div class="tp-card__body"><div class="team-list">
                        @forelse($teams as $team)
                            <article class="team-row">
                                <div class="team-row__main"><span class="team-avatar"><i class="fa fa-users"></i></span><div class="team-row__text"><h3 title="{{ $team->name }}">{{ $team->name }}</h3><div class="team-row__meta"><span><i class="fa fa-users"></i> {{ $team->users_count }} thành viên</span><span><i class="fa fa-calendar"></i> {{ $team->created_at->format('d/m/Y') }}</span></div></div></div>
                                <div class="tp-inline-actions team-row__actions"><a href="{{ route('teams.show', $team) }}" class="tp-btn tp-btn--small"><i class="fa fa-eye"></i> Xem</a><a href="{{ route('teams.edit', $team) }}" class="tp-btn tp-btn--small"><i class="fa fa-edit"></i> Sửa</a><form action="{{ route('teams.destroy', $team) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa đội nhóm này không?');">@csrf @method('DELETE')<button type="submit" class="tp-btn tp-btn--danger tp-btn--small"><i class="fa fa-trash"></i> Xóa</button></form></div>
                            </article>
                        @empty
                            <div class="tp-empty"><i class="fa fa-users fa-2x"></i><h3>Chưa có đội nhóm</h3><p>Tạo đội nhóm đầu tiên để bắt đầu quản lý thành viên.</p><a href="{{ route('teams.create') }}" class="tp-btn tp-btn--primary"><i class="fa fa-plus"></i> Tạo đội nhóm</a></div>
                        @endforelse
                    </div></div>
                </section>
            </div>
            <aside class="team-sidebar">@include('partials.right_sidebar')</aside>
        </div>
    </div>
</section>
@endsection
