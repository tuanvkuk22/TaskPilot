@extends('layouts.app')

@section('content')
<div class="col-md-8 col-md-offset-2">
    <div class="panel panel-default">
        <div class="panel-heading">Chỉnh sửa đội nhóm</div>
        <div class="panel-body">
            <form method="POST" action="{{ route('teams.update', $team) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Tên đội nhóm</label>
                    <input id="name" name="name" class="form-control" value="{{ old('name', $team->name) }}" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description" class="form-control" rows="5">{{ old('description', $team->description) }}</textarea>
                </div>
                <a href="{{ route('teams.show', $team) }}" class="btn btn-default">Hủy</a>
                <button class="btn btn-primary">Lưu thay đổi</button>
            </form>
        </div>
    </div>
</div>
@endsection
