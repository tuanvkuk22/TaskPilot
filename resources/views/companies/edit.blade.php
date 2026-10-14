@extends('layouts.app')
@section('content')
<section class="tp-page company-page"><div class="tp-page-shell tp-page-shell--narrow">
    <header class="tp-page-head"><div><span class="tp-eyebrow">CUSTOMERS</span><h1>Chỉnh sửa {{ $company->name }}</h1><p>Cập nhật hồ sơ và thông tin liên hệ khách hàng.</p></div><a href="{{ route('companies.show',$company) }}" class="tp-btn">Quay lại</a></header>
    <section class="tp-surface tp-form"><div class="tp-surface-body"><form method="POST" action="{{ route('companies.update',$company) }}">@csrf @method('PUT') @include('companies.partials.form',['company'=>$company])<div class="tp-actions"><button class="tp-btn tp-btn--primary">Lưu thay đổi</button><a href="{{ route('companies.show',$company) }}" class="tp-btn">Hủy</a></div></form></div></section>
</div></section>
@endsection
