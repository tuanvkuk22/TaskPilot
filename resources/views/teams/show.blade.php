@extends('layouts.app')
@section('content')
<div class="team-page" style="max-width:1400px;margin:0 auto;padding:25px 20px 50px;">

    <div style="display:flex;align-items:center;min-width:0;font-size:13px;color:#6b778c;margin-bottom:18px;">
        <a href="{{ route('teams.index') }}" style="color:#0c66e4;text-decoration:none;">
            <i class="fa fa-users"></i> Đội nhóm
        </a>
        <span style="margin:0 7px;color:#a5adba;">/</span>
        <span title="{{ $team->name }}" style="min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $team->name }}</span>
    </div>

    <div style="background:#fff;border:1px solid #dfe1e6;border-radius:10px;padding:24px;margin-bottom:18px;box-shadow:0 2px 8px rgba(9,30,66,.06);">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:25px;">
            <div style="display:flex;align-items:center;gap:16px;min-width:0;">
                <div style="width:58px;height:58px;min-width:58px;display:flex;align-items:center;justify-content:center;background:#deebff;color:#0c66e4;border-radius:12px;font-size:22px;">
                    <i class="fa fa-users"></i>
                </div>
                <div style="min-width:0;flex:1;">
                    <h1 title="{{ $team->name }}" style="margin:0 0 6px;overflow:hidden;font-size:26px;font-weight:700;color:#172b4d;text-overflow:ellipsis;white-space:nowrap;">{{ $team->name }}</h1>
                    <p style="margin:0 0 10px;color:#6b778c;font-size:13px;overflow-wrap:anywhere;">{{ $team->description ?: 'Chưa có mô tả cho đội nhóm này.' }}</p>
                    <div style="display:flex;align-items:center;gap:18px;color:#6b778c;font-size:12px;">
                        <span><i class="fa fa-calendar" style="color:#0c66e4;"></i> {{ $team->created_at->format('d/m/Y') }}</span>
                        <span><i class="fa fa-users" style="color:#0c66e4;"></i> {{ $team->users->count() }} thành viên</span>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:8px;flex-shrink:0;">
                <a href="{{ route('teams.index') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Quay lại
                </a>
                @if(Route::has('teams.edit'))
                    <a href="{{ route('teams.edit', $team) }}" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Chỉnh sửa
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:18px;">
        <div style="background:#fff;border:1px solid #dfe1e6;border-radius:10px;padding:18px;box-shadow:0 2px 7px rgba(9,30,66,.05);">
            <div style="display:flex;align-items:center;gap:13px;">
                <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:#deebff;color:#0c66e4;border-radius:9px;">
                    <i class="fa fa-users"></i>
                </div>
                <div>
                    <div style="font-size:12px;color:#6b778c;">Thành viên</div>
                    <strong style="display:block;font-size:20px;color:#172b4d;">{{ $team->users->count() }}</strong>
                    <small style="color:#97a0af;">người trong đội</small>
                </div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #dfe1e6;border-radius:10px;padding:18px;box-shadow:0 2px 7px rgba(9,30,66,.05);">
            <div style="display:flex;align-items:center;gap:13px;">
                <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:#eae6ff;color:#6554c0;border-radius:9px;">
                    <i class="fa fa-user-shield"></i>
                </div>
                <div>
                    <div style="font-size:12px;color:#6b778c;">Quản trị viên</div>
                    <strong style="display:block;font-size:20px;color:#172b4d;">{{ $team->users->filter(fn($user) => in_array($user->pivot->role ?? '', ['admin','owner']))->count() }}</strong>
                    <small style="color:#97a0af;">người quản trị</small>
                </div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #dfe1e6;border-radius:10px;padding:18px;box-shadow:0 2px 7px rgba(9,30,66,.05);">
            <div style="display:flex;align-items:center;gap:13px;">
                <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:#dffcf0;color:#00875a;border-radius:9px;">
                    <i class="fa fa-calendar-check"></i>
                </div>
                <div>
                    <div style="font-size:12px;color:#6b778c;">Ngày thành lập</div>
                    <strong style="display:block;font-size:18px;color:#172b4d;">{{ $team->created_at->format('d/m/Y') }}</strong>
                    <small style="color:#97a0af;">ngày tạo đội nhóm</small>
                </div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,1.7fr) minmax(320px,1fr);gap:18px;align-items:start;">

        <div style="background:#fff;border:1px solid #dfe1e6;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(9,30,66,.05);">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #ebecf0;">
                <div>
                    <h2 style="margin:0 0 4px;font-size:15px;color:#172b4d;font-weight:700;">
                        <i class="fa fa-users" style="color:#0c66e4;"></i> Thành viên
                    </h2>
                    <p style="margin:0;color:#6b778c;font-size:12px;">Danh sách thành viên đang tham gia đội nhóm</p>
                </div>
                <span style="padding:5px 10px;background:#deebff;color:#0c66e4;border-radius:6px;font-size:12px;font-weight:700;">
                    {{ $team->users->count() }}
                </span>
            </div>

            @forelse($team->users as $user)
                @php $role = $user->pivot->role ?? 'member'; @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 20px;border-bottom:1px solid #ebecf0;">
                    <div style="display:flex;align-items:center;gap:11px;min-width:0;">
                        <div style="width:40px;height:40px;min-width:40px;display:flex;align-items:center;justify-content:center;background:#0c66e4;color:#fff;border-radius:50%;font-size:13px;font-weight:700;">
                            {{ strtoupper(substr($user->name ?? $user->email,0,1)) }}
                        </div>
                        <div>
                            <strong style="display:block;color:#172b4d;font-size:13px;">{{ $user->name ?? 'Chưa cập nhật tên' }}</strong>
                            <span style="display:block;margin-top:3px;color:#6b778c;font-size:12px;">{{ $user->email }}</span>
                        </div>
                    </div>

                    @if($role === 'owner')
                        <span style="padding:6px 9px;background:#fff0b3;color:#7f5f01;border:1px solid #ffe380;border-radius:6px;font-size:10px;font-weight:600;">
                            <i class="fa fa-crown"></i> Chủ đội
                        </span>
                    @elseif($role === 'admin')
                        <span style="padding:6px 9px;background:#eae6ff;color:#403294;border:1px solid #d9d2ff;border-radius:6px;font-size:10px;font-weight:600;">
                            <i class="fa fa-shield-alt"></i> Quản trị viên
                        </span>
                    @else
                        <span style="padding:6px 9px;background:#f4f5f7;color:#42526e;border:1px solid #dfe1e6;border-radius:6px;font-size:10px;font-weight:600;">
                            <i class="fa fa-user"></i> Thành viên
                        </span>
                    @endif
                </div>
            @empty
                <div style="padding:55px 20px;text-align:center;color:#6b778c;">
                    <i class="fa fa-users" style="font-size:28px;color:#b3bac5;margin-bottom:10px;"></i>
                    <strong style="display:block;color:#172b4d;font-size:14px;">Chưa có thành viên</strong>
                    <span style="font-size:12px;">Hãy mời thành viên đầu tiên vào đội nhóm.</span>
                </div>
            @endforelse
        </div>

        <div class="tp-team-invite-card">
            <div class="tp-team-invite-header">
                <h2>
                    <i class="fa fa-user-plus"></i> Mời thành viên
                </h2>
                <p>Thêm người vào đội nhóm</p>
            </div>

            <div class="tp-team-invite-body">
                <div class="tp-team-invite-banner">
                    <div class="tp-team-invite-icon"><i class="fa fa-paper-plane"></i></div>
                    <div>
                        <strong>Mời thành viên mới</strong>
                        <span>Nhập username hoặc email của người bạn muốn mời.</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('teams.invite',$team) }}" id="team-invite-form" class="tp-team-invite-form">
                    @csrf
                    <label class="tp-team-invite-label">Username hoặc email</label>
                    <div id="invite-box" class="tp-invite-box">
                        <div id="invite-tags" class="tp-invite-tags"></div>
                        <input id="invite-input" class="tp-invite-input" type="text" placeholder="Nhập username hoặc email..." autocomplete="off">
                    </div>
                    <input type="hidden" id="team-members" name="members">
                    <small class="tp-team-invite-help">Nhập username hoặc email, nhấn Enter để thêm.</small>
                    <button type="submit" class="btn btn-primary btn-block tp-invite-submit"><i class="fa fa-paper-plane"></i> Gửi lời mời</button>
                </form>

                <div class="tp-team-invite-note">
                    <i class="fa fa-info-circle"></i>
                    <span>Thành viên sẽ nhận được thông báo sau khi lời mời được gửi.</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('jqueryScript')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const box=document.getElementById('invite-box'),input=document.getElementById('invite-input'),tags=document.getElementById('invite-tags'),hidden=document.getElementById('team-members'),form=document.getElementById('team-invite-form');
    let members=[];
    function render(){
        tags.innerHTML='';
        members.forEach((m,i)=>{
            const tag=document.createElement('span');
            tag.className='tp-member-tag';
            tag.innerHTML = m + ' <button type="button" data-i="' + i + '">×</button>';
            tags.appendChild(tag);
        });
        hidden.value=members.join(',');
    }
    function add(){
        const v=input.value.trim();
        if(v&&!members.includes(v)){members.push(v);render();}
        input.value='';input.focus();
    }
    input.addEventListener('keydown',e=>{
        if(e.key==='Enter'||e.key===','){e.preventDefault();add();}
        if(e.key==='Backspace'&&!input.value&&members.length){members.pop();render();}
    });
    tags.addEventListener('click',e=>{
        if(e.target.dataset.i!==undefined){members.splice(+e.target.dataset.i,1);render();input.focus();}
    });
    box.addEventListener('click',()=>input.focus());
    form.addEventListener('submit',(event)=>{
        if(input.value.trim()) add();
        if(!members.length){
            event.preventDefault();
            input.focus();
            input.setCustomValidity('Nhập email hoặc username trước khi gửi lời mời.');
            input.reportValidity();
            input.setCustomValidity('');
        }
    });
});
</script>
@endsection
