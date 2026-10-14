@extends('layouts.app')
@section('content')
<div style="width:100%;padding:24px 28px;background:#f7f8fa;box-sizing:border-box;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
            <h1 style="margin:0;color:#172b4d;font-size:28px;font-weight:700;line-height:1.25;">Đội nhóm</h1>
            <p style="margin:5px 0 0;color:#6b778c;font-size:14px;">Quản lý đội nhóm và thành viên trong hệ thống</p>
        </div>
        <a href="{{ route('teams.create') }}" style="display:inline-flex;align-items:center;gap:6px;height:40px;padding:0 16px;background:#0052cc;color:#fff;border:1px solid #0052cc;border-radius:6px;text-decoration:none;font-size:15px;font-weight:500;">
            <i class="fa fa-plus"></i>Tạo đội nhóm
        </a>
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,3fr) minmax(260px,1fr);gap:24px;align-items:start;">
        <div>
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:14px;padding:18px;background:#fff;border:1px solid #ebecf0;border-radius:8px;box-shadow:0 1px 2px rgba(9,30,66,.08);">
                    <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:#e9f2ff;color:#0052cc;border-radius:6px;"><i class="fa fa-users"></i></div>
                    <div>
                        <div style="color:#6b778c;font-size:13px;">Tổng đội nhóm</div>
                        <strong style="display:block;margin-top:2px;color:#172b4d;font-size:22px;line-height:1.2;">{{ $teams->count() }}</strong>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:14px;padding:18px;background:#fff;border:1px solid #ebecf0;border-radius:8px;box-shadow:0 1px 2px rgba(9,30,66,.08);">
                    <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:#e3fcef;color:#00875a;border-radius:6px;"><i class="fa fa-user"></i></div>
                    <div>
                        <div style="color:#6b778c;font-size:13px;">Tổng thành viên</div>
                        <strong style="display:block;margin-top:2px;color:#172b4d;font-size:22px;line-height:1.2;">{{ $teams->sum('users_count') }}</strong>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:14px;padding:18px;background:#fff;border:1px solid #ebecf0;border-radius:8px;box-shadow:0 1px 2px rgba(9,30,66,.08);">
                    <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:#fffae6;color:#974f00;border-radius:6px;"><i class="fa fa-user-plus"></i></div>
                    <div>
                        <div style="color:#6b778c;font-size:13px;">Đội nhóm đang tham gia</div>
                        <strong style="display:block;margin-top:2px;color:#172b4d;font-size:22px;line-height:1.2;">{{ $teams->count() }}</strong>
                    </div>
                </div>
            </div>

            <div style="background:#fff;border:1px solid #ebecf0;border-radius:8px;box-shadow:0 1px 2px rgba(9,30,66,.08);overflow:hidden;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:18px;border-bottom:1px solid #ebecf0;">
                    <div>
                        <h2 style="margin:0 0 4px;color:#172b4d;font-size:18px;font-weight:600;">Danh sách đội nhóm</h2>
                        <p style="margin:0;color:#6b778c;font-size:13px;">Các đội nhóm bạn đang tham gia</p>
                    </div>
                    <span style="padding:5px 8px;color:#0747a6;background:#deebff;border-radius:4px;font-size:12px;font-weight:600;">{{ $teams->count() }} đội nhóm</span>
                </div>

                <div style="padding:18px;">
                   @forelse($teams as $team)
    <div style="padding:20px;border:1px solid #ebecf0;border-radius:8px;background:#fff;margin-bottom:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
            <div style="display:flex;align-items:center;gap:14px;min-width:0;">
                <div style="width:46px;height:46px;flex:0 0 46px;display:flex;align-items:center;justify-content:center;color:#0052cc;background:#e9f2ff;border-radius:8px;">
                    <i class="fa fa-users"></i>
                </div>

                <div style="min-width:0;">
                    <h3 title="{{ $team->name }}" style="max-width:100%;margin:0 0 7px;overflow:hidden;color:#172b4d;font-size:17px;font-weight:600;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $team->name }}
                    </h3>

                    <div style="display:flex;align-items:center;gap:18px;color:#6b778c;font-size:13px;">
                        <span>
                            <i class="fa fa-users me-1"></i>
                            {{ $team->users_count }} thành viên
                        </span>
                        <span>
                            <i class="fa fa-calendar me-1"></i>
                            {{ $team->created_at->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>

      
            <div style="display:flex;align-items:center;gap:8px;flex:0 0 auto;">
    <a href="{{ route('teams.show', $team) }}" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 12px;color:#0052cc;background:#fff;border:1px solid #0052cc;border-radius:6px;text-decoration:none;font-size:13px;font-weight:500;">
        <i class="fa fa-eye"></i>Xem chi tiết
    </a>

    <a href="{{ route('teams.edit', $team) }}" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 12px;color:#42526e;background:#fff;border:1px solid #dfe1e6;border-radius:6px;text-decoration:none;font-size:13px;font-weight:500;">
        <i class="fa fa-edit"></i>Sửa
    </a>

    <form action="{{ route('teams.destroy', $team) }}" method="POST" style="margin:0;" onsubmit="return confirm('Bạn có chắc muốn xóa đội nhóm này không?');">
        @csrf
        @method('DELETE')
        <button type="submit" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 12px;color:#de350b;background:#fff;border:1px solid #de350b;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;">
            <i class="fa fa-trash"></i>Xóa
        </button>
    </form>
</div>
        </div>
    </div>
@empty
    <div style="text-align:center;padding:50px 20px;">
        <div style="width:64px;height:64px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;color:#6b778c;background:#f4f5f7;border-radius:50%;font-size:24px;">
            <i class="fa fa-users"></i>
        </div>
        <h3 style="margin:0 0 5px;color:#172b4d;font-size:18px;">Chưa có đội nhóm</h3>
        <p style="margin:0 0 18px;color:#6b778c;font-size:14px;">Tạo đội nhóm đầu tiên để bắt đầu quản lý thành viên.</p>
        <a href="{{ route('teams.create') }}" style="display:inline-flex;align-items:center;gap:6px;height:40px;padding:0 16px;background:#0052cc;color:#fff;border-radius:6px;text-decoration:none;font-size:14px;">
            <i class="fa fa-plus"></i>Tạo đội nhóm
        </a>
    </div>
@endforelse
                </div>
            </div>
        </div>

        <div>
            @include('partials.right_sidebar')
        </div>
    </div>
</div>
@endsection
