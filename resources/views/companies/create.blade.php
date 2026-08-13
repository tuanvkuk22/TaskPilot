@extends('layouts.app')
@section('content')
<section class="tp-page-head"><div><span class="tp-eyebrow">CUSTOMERS</span><h1>Thêm khách hàng</h1><p>Lưu thông tin liên hệ để dùng trong các dự án TaskPilot.</p></div><a href="{{ route('companies.index') }}" class="btn btn-default">Quay lại</a></section>
<section class="tp-surface tp-form"><div class="tp-surface-body"><form method="POST" action="{{ route('companies.store') }}">@csrf @include('companies.partials.form',['company'=>null])<div class="tp-actions"><button class="btn btn-primary">Lưu khách hàng</button><a href="{{ route('companies.index') }}" class="btn btn-default">Hủy</a></div></form></div></section>
@endsection
