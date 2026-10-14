@extends('layouts.app')
@section('content')
<section class="tp-page-head">
    <div><span class="tp-eyebrow">CUSTOMERS</span><h1>Khách hàng</h1><p>Quản lý thông tin liên hệ và các dự án theo từng khách hàng.</p></div>
    <a href="{{ route('companies.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Thêm khách hàng</a>
</section>

<section class="tp-surface">
    <div class="tp-surface-header"><strong>{{ $companies->count() }} khách hàng</strong></div>
    <div class="table-responsive">
        <table class="table tp-table">
            <thead><tr><th>Khách hàng</th><th>Liên hệ</th><th>Dự án</th><th></th></tr></thead>
            <tbody>
            @forelse($companies as $company)
                <tr>
                    <td><a href="{{ route('companies.show', $company) }}">{{ $company->name }}</a><span class="tp-meta">{{ \Illuminate\Support\Str::limit($company->description, 70) }}</span></td>
                    <td>{{ $company->contact_email ?: 'Chưa có email' }}<span class="tp-meta">{{ $company->contact_phone ?: 'Chưa có số điện thoại' }}</span></td>
                    <td><span class="tp-badge">{{ $company->projects_count }} dự án</span></td>
                    <td class="text-right" style="white-space:nowrap;">
                        <a class="btn btn-default btn-sm" href="{{ route('companies.edit', $company) }}">Chỉnh sửa</a>
                        @if($company->projects_count === 0)
                            <form method="POST" action="{{ route('companies.destroy', $company) }}" style="display:inline-block;margin-left:6px;" onsubmit="return confirm('Xóa khách hàng {{ addslashes($company->name) }}? Thao tác này không thể hoàn tác.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-default btn-sm text-danger"><i class="fa fa-trash"></i> Xóa</button>
                            </form>
                        @else
                            <span class="text-muted" style="margin-left:8px;font-size:12px;" title="Không thể xóa khách hàng đang có dự án">Đang có dự án</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="tp-empty"><i class="fa fa-building-o"></i>Chưa có khách hàng. Thêm khách hàng đầu tiên để gắn với dự án.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
