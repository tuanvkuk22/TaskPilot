@extends('layouts.auth')

@section('content')
<div id="login-app"></div>
@endsection

@section('page-style')
<link href="{{ asset('build/assets/login.css') }}?v={{ file_exists(public_path('build/assets/login.css')) ? filemtime(public_path('build/assets/login.css')) : time() }}" rel="stylesheet">
@endsection

@section('auth-scripts')
<script>
    window.loginData = {
        csrf_token: @json(csrf_token()),
        errors: @json($errors->all()),
        old: @json(old()),
        login_url: @json(route('login')),
        google_redirect_url: @json(route('google.redirect')),
        password_reset_url: @json(route('password.request')),
        register_url: @json(route('register')),
        status: @json(session('status')),
    };
</script>
<script type="module" src="{{ asset('build/assets/login.js') }}?v={{ file_exists(public_path('build/assets/login.js')) ? filemtime(public_path('build/assets/login.js')) : time() }}"></script>
@endsection
