@extends('layouts.app')

@push('styles')
<style>
body {
    background: #f4f7fc;
}
.auth-page {
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 0;
}
.auth-card {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
    max-width: 980px;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 28px 80px rgba(34, 60, 80, 0.12);
    background: #ffffff;
}
.auth-side {
    flex: 1 1 360px;
    min-width: 280px;
    background: linear-gradient(135deg, #2a4d96 0%, #3f6fc5 100%);
    color: #ffffff;
    padding: 48px 36px;
    position: relative;
}
.auth-side::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 40%;
    background: rgba(255, 255, 255, 0.06);
    pointer-events: none;
}
.auth-side h2 {
    margin-bottom: 16px;
    font-size: 32px;
    line-height: 1.1;
}
.auth-side p {
    margin-bottom: 24px;
    color: rgba(255, 255, 255, 0.82);
    line-height: 1.7;
}
.auth-side .feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.auth-side .feature-list li {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
    font-size: 15px;
    color: rgba(255, 255, 255, 0.95);
}
.auth-side .feature-list li i {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.15);
}
.auth-form {
    flex: 1 1 420px;
    min-width: 320px;
    padding: 48px 42px;
}
.auth-form h3 {
    margin-bottom: 10px;
    font-size: 26px;
    color: #222f4b;
}
.auth-form p.lead {
    margin-bottom: 28px;
    color: #6b778c;
}
.form-control {
    border-radius: 14px;
    border: 1px solid #d9e2ef;
    padding: 14px 16px;
    transition: border-color .2s ease, box-shadow .2s ease;
}
.form-control:focus {
    border-color: #3f6fc5;
    box-shadow: 0 0 0 3px rgba(63, 111, 197, 0.12);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 10px;
    color: #42506b;
    font-weight: 600;
}
.input-group {
    position: relative;
}
.form-error {
    margin-top: 10px;
    font-size: 13px;
    color: #cc4b37;
}
.btn-primary {
    background: #315ad0;
    border-color: #315ad0;
    border-radius: 14px;
    padding: 14px 18px;
    font-weight: 600;
}
.btn-primary:hover {
    background: #243f8a;
    border-color: #243f8a;
}
.btn-google {
    background: #ffffff;
    border: 1px solid #d9e2ef;
    color: #2f3c56;
    border-radius: 14px;
    padding: 14px 18px;
    font-weight: 600;
}
.btn-google:hover {
    background: #f7f9fd;
    color: #1d2b45;
}
.btn-google i {
    margin-right: 10px;
}
.divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 28px 0;
}
.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e8eef7;
}
.divider:not(:empty)::before {
    margin-right: .75em;
}
.divider:not(:empty)::after {
    margin-left: .75em;
}
.divider span {
    color: #6b778c;
    font-size: 13px;
    letter-spacing: .08em;
}
.alert {
    border-radius: 16px;
    padding: 16px 20px;
    font-size: 14px;
}
.auth-help {
    margin-top: 18px;
    color: #6b778c;
    font-size: 14px;
}
.auth-help a {
    color: #315ad0;
    text-decoration: none;
}
.auth-help a:hover {
    text-decoration: underline;
}
@media (max-width: 767px) {
    .auth-card {
        flex-direction: column;
    }
    .auth-side,
    .auth-form {
        padding: 32px 28px;
    }
}
</style>
@endpush

@section('content')
<div class="container auth-page">
    <div class="auth-card">
        <div class="auth-side">
            <h2>TaskPilot</h2>
            <p>Đăng nhập vào nền tảng quản lý dự án và công việc chuyên nghiệp. Tập trung vào thành tích, phối hợp dễ dàng và khởi tạo dự án nhanh chóng.</p>

            <ul class="feature-list">
                <li><i class="fa fa-check"></i> Giao diện tối ưu cho người dùng</li>
                <li><i class="fa fa-check"></i> Bảo mật đăng nhập Username + Password</li>
                <li><i class="fa fa-check"></i> Hỗ trợ Google SSO nhanh chóng</li>
            </ul>
        </div>

        <div class="auth-form">
            <h3>Đăng nhập</h3>
            <p class="lead">Sử dụng tài khoản TaskPilot hoặc Google để bắt đầu.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                {{ csrf_field() }}

                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" autofocus required>
                    @if ($errors->has('username'))
                        <div class="form-error">{{ $errors->first('username') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                    @if ($errors->has('password'))
                        <div class="form-error">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                <div class="form-group" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <label style="font-size:14px; color:#556680;">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Ghi nhớ đăng nhập
                    </label>
                    <a href="{{ route('password.request') }}" style="font-size:14px; color:#315ad0;">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>

                <div class="divider"><span>HOẶC</span></div>

                <a class="btn btn-google btn-block" href="{{ route('auth.google') }}">
                    <i class="fa fa-google" aria-hidden="true"></i> Đăng nhập bằng Google
                </a>
            </form>

            <div class="auth-help">
                Nếu bạn không có tài khoản TaskPilot, vui lòng liên hệ quản trị viên để đăng ký.
            </div>
        </div>
    </div>
</div>
@endsection
