@extends('layouts.app')

@section('content')
<section class="tp-page company-page"><div class="tp-page-shell">
    <header class="tp-page-head"><div><span class="tp-eyebrow">CUSTOMERS</span><h1>Khách hàng</h1><p>Quản lý thông tin liên hệ và các dự án theo từng khách hàng.</p></div><a href="{{ route('companies.create') }}" class="tp-btn tp-btn--primary"><i class="fa fa-plus"></i> Thêm khách hàng</a></header>
    <section class="tp-card"><div class="tp-card__header"><strong>{{ $companies->count() }} khách hàng</strong></div><div class="table-responsive"><table class="table tp-table"><thead><tr><th>Khách hàng</th><th>Liên hệ</th><th>Dự án</th><th class="tp-table__actions"></th></tr></thead><tbody>
        @forelse($companies as $company)
            <tr><td><a href="{{ route('companies.show', $company) }}">{{ $company->name }}</a><span class="tp-meta">{{ strlen((string) $company->description) > 70 ? substr((string) $company->description, 0, 70).'…' : $company->description }}</span></td><td>{{ $company->contact_email ?: 'Chưa có email' }}<span class="tp-meta">{{ $company->contact_phone ?: 'Chưa có số điện thoại' }}</span></td><td><span class="tp-badge">{{ $company->projects_count }} dự án</span></td><td class="tp-table__actions"><div class="tp-inline-actions"><a class="tp-btn tp-btn--small" href="{{ route('companies.edit', $company) }}">Chỉnh sửa</a>@if($company->projects_count === 0)<form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Xóa khách hàng {{ addslashes($company->name) }}? Thao tác này không thể hoàn tác.');">@csrf @method('DELETE')<button type="submit" class="tp-btn tp-btn--danger tp-btn--small"><i class="fa fa-trash"></i> Xóa</button></form>@else<span class="tp-meta">Đang có dự án</span>@endif</div></td></tr>
        @empty
            <tr><td colspan="4"><div class="tp-empty"><i class="fa fa-building-o fa-2x"></i><p>Chưa có khách hàng. Thêm khách hàng đầu tiên để gắn với dự án.</p></div></td></tr>
        @endforelse
    </tbody></table></div></section>
</div></section>
@endsection
