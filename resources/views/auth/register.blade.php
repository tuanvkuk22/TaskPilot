@extends('layouts.auth')

@section('title', 'Đăng ký')

@section('page-style')
<link href="{{ asset('build/assets/login.css') }}?v={{ file_exists(public_path('build/assets/login.css')) ? filemtime(public_path('build/assets/login.css')) : time() }}" rel="stylesheet">
@endsection

@section('content')
<main class="tp-login-container">
    <section class="tp-login-card tp-register-card" aria-labelledby="register-title">
        <a href="{{ route('welcome') }}" class="tp-login-brand tp-register-brand" aria-label="Về trang chủ TaskPilot">
            <span class="tp-login-logo"><i class="fa-solid fa-square-check"></i></span>
            <span>TaskPilot</span>
        </a>

        <h1 id="register-title" class="tp-login-title">Tạo workspace của bạn</h1>
        <p class="tp-login-subtitle">Bắt đầu quản lý dự án, công việc và đội ngũ trong vài phút.</p>

        <div class="tp-register-benefits" aria-label="Quyền lợi khi đăng ký">
            <span><i class="fa-solid fa-circle-check"></i> Tạo dự án đầu tiên miễn phí</span>
            <span><i class="fa-solid fa-circle-check"></i> Mời đội ngũ cùng cộng tác</span>
        </div>

        @if ($errors->any())
            <div class="tp-login-alert tp-login-alert-error" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Vui lòng kiểm tra lại thông tin đăng ký.</span>
            </div>
        @endif

        <form class="tp-login-form" method="POST" action="{{ route('register') }}">
            @csrf

            <div class="tp-form-row">
                <label for="name" class="tp-form-label">Họ và tên</label>
                <input id="name" type="text" class="tp-form-control {{ $errors->has('name') ? 'tp-form-control-error' : '' }}" name="name" value="{{ old('name') }}" placeholder="Nhập họ và tên của bạn" required autofocus autocomplete="name">
                @error('name')
                    <span class="tp-form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="tp-form-row">
                <label for="email" class="tp-form-label">Email công việc</label>
                <input id="email" type="email" class="tp-form-control {{ $errors->has('email') ? 'tp-form-control-error' : '' }}" name="email" value="{{ old('email') }}" placeholder="ban@congty.com" required autocomplete="email">
                @error('email')
                    <span class="tp-form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="tp-form-row">
                <label for="password" class="tp-form-label">Mật khẩu</label>
                <div class="tp-password-wrapper">
                    <input id="password" type="password" class="tp-form-control {{ $errors->has('password') ? 'tp-form-control-error' : '' }}" name="password" placeholder="Ít nhất 6 ký tự" required autocomplete="new-password">
                    <button type="button" class="tp-password-toggle" data-password-toggle="password" aria-label="Hiện mật khẩu" aria-pressed="false"><i class="fa-solid fa-eye-slash"></i></button>
                </div>
                <span class="tp-register-password-hint">Dùng tối thiểu 6 ký tự để bảo vệ tài khoản.</span>
                @error('password')
                    <span class="tp-form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                @enderror
            </div>

            <div class="tp-form-row">
                <label for="password-confirm" class="tp-form-label">Xác nhận mật khẩu</label>
                <div class="tp-password-wrapper">
                    <input id="password-confirm" type="password" class="tp-form-control" name="password_confirmation" placeholder="Nhập lại mật khẩu" required autocomplete="new-password">
                    <button type="button" class="tp-password-toggle" data-password-toggle="password-confirm" aria-label="Hiện mật khẩu xác nhận" aria-pressed="false"><i class="fa-solid fa-eye-slash"></i></button>
                </div>
            </div>

            <button type="submit" class="tp-btn tp-btn-primary"><span>Tạo tài khoản miễn phí</span><i class="fa-solid fa-arrow-right"></i></button>
        </form>

        <p class="tp-register-note"><i class="fa-solid fa-shield-halved"></i> Thông tin của bạn được bảo vệ an toàn.</p>

        <div class="tp-login-footer"><span>Đã có tài khoản?</span> <a href="{{ route('login') }}" class="tp-login-footer-link">Đăng nhập</a></div>
    </section>
</main>
@endsection

@section('auth-scripts')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.dataset.passwordToggle);
            var isVisible = input.type === 'text';
            input.type = isVisible ? 'password' : 'text';
            button.setAttribute('aria-pressed', String(!isVisible));
            button.setAttribute('aria-label', isVisible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
            button.querySelector('i').className = 'fa-solid ' + (isVisible ? 'fa-eye-slash' : 'fa-eye');
        });
    });
</script>
@endsection
