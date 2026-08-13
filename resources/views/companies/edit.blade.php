@extends('layouts.app')
@section('content')
<section class="tp-page-head"><div><span class="tp-eyebrow">CUSTOMERS</span><h1>Chỉnh sửa {{ $company->name }}</h1><p>Cập nhật hồ sơ và thông tin liên hệ khách hàng.</p></div><a href="{{ route('companies.show',$company) }}" class="btn btn-default">Quay lại</a></section>
<section class="tp-surface tp-form"><div class="tp-surface-body"><form method="POST" action="{{ route('companies.update',$company) }}">@csrf @method('PUT') @include('companies.partials.form',['company'=>$company])<div class="tp-actions"><button class="btn btn-primary">Lưu thay đổi</button><a href="{{ route('companies.show',$company) }}" class="btn btn-default">Hủy</a></div></form></div></section>
@endsection
