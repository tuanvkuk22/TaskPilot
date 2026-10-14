@extends('layouts.app')

@section('content')
<section class="tp-page team-page"><div class="tp-page-shell tp-page-shell--narrow">
    <header class="tp-page-head"><div><span class="tp-eyebrow">WORKSPACE</span><h1>Chỉnh sửa đội nhóm</h1><p>Cập nhật tên và mô tả để mọi người dễ nhận biết đội.</p></div><a href="{{ route('teams.show', $team) }}" class="tp-btn"><i class="fa fa-arrow-left"></i> Quay lại</a></header>
    <section class="tp-card tp-form"><div class="tp-card__body"><form method="POST" action="{{ route('teams.update', $team) }}">@csrf @method('PUT')
        <div class="form-group"><label for="name">Tên đội nhóm</label><input id="name" name="name" class="form-control" value="{{ old('name', $team->name) }}" required maxlength="255"></div>
        <div class="form-group"><label for="description">Mô tả</label><textarea id="description" name="description" class="form-control" rows="5">{{ old('description', $team->description) }}</textarea></div>
        <div class="tp-actions"><a href="{{ route('teams.show', $team) }}" class="tp-btn">Hủy</a><button class="tp-btn tp-btn--primary">Lưu thay đổi</button></div>
    </form></div></section>
</div></section>
@endsection
