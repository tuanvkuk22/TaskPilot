@extends('layouts.app')

@section('content')
<div class="tp-permissions-page">
    <div class="tp-permissions-header">
        <div>
            <div class="tp-project-breadcrumb"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a> <span>/</span> Quản lý quyền</div>
            <h1><i class="fa fa-shield"></i> Chỉnh sửa quyền</h1>
            <p>Thiết lập các quyền và vai trò được phép sử dụng trong dự án.</p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="tp-project-btn"><i class="fa fa-arrow-left"></i> Quay lại dự án</a>
    </div>

    <div class="tp-permissions-layout">
        <section class="tp-jira-card">
            <div class="tp-jira-card-header"><h2 class="tp-jira-card-title">Quyền hiện có</h2></div>
            <div class="tp-jira-card-body">
                @forelse($project->permissions as $permission)
                    <form action="{{ route('projects.permissions.update', [$project, $permission]) }}" method="POST" class="tp-permission-editor">
                        @csrf
                        @method('PATCH')
                        <div class="tp-permission-editor-top">
                            <input type="text" name="name" value="{{ old('name', $permission->name) }}" maxlength="100" required class="form-control">
                            <button class="tp-project-btn tp-project-btn-primary" type="submit"><i class="fa fa-save"></i> Lưu</button>
                        </div>
                        <div class="tp-permission-role-options">
                            @foreach($project->roles as $role)
                                <label><input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ in_array($role->name, old('roles', $permission->roles ?? []), true) ? 'checked' : '' }}> {{ $role->name }}</label>
                            @endforeach
                        </div>
                        @error('permission_' . $permission->id)<div class="text-danger small">{{ $message }}</div>@enderror
                    </form>
                    <form action="{{ route('projects.permissions.destroy', [$project, $permission]) }}" method="POST" class="tp-permission-delete" onsubmit="return confirm('Xóa quyền {{ addslashes($permission->name) }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link text-danger"><i class="fa fa-trash"></i> Xóa quyền</button>
                    </form>
                @empty
                    <p class="text-muted mb-0">Chưa có quyền nào cho dự án này.</p>
                @endforelse
            </div>
        </section>

        <section class="tp-jira-card">
            <div class="tp-jira-card-header"><h2 class="tp-jira-card-title">Thêm quyền mới</h2></div>
            <div class="tp-jira-card-body">
                <form action="{{ route('projects.permissions.store', $project) }}" method="POST" class="tp-permission-form">
                    @csrf
                    <div class="mb-3">
                        <label for="permission-name">Tên quyền</label>
                        <input id="permission-name" type="text" name="name" value="{{ old('name') }}" maxlength="100" required class="form-control @error('permission_name') is-invalid @enderror" placeholder="Ví dụ: Duyệt ngân sách">
                        @error('permission_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <fieldset class="mb-3">
                        <legend>Vai trò được cấp quyền</legend>
                        @foreach($project->roles as $role)
                            <label><input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ in_array($role->name, old('roles', []), true) ? 'checked' : '' }}> {{ $role->name }}</label>
                        @endforeach
                        @error('roles')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </fieldset>
                    <button type="submit" class="tp-project-btn tp-project-btn-primary w-100"><i class="fa fa-plus"></i> Thêm quyền</button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
