<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TaskPilot') }} — Workspace Quản lý Dự án & Tiến độ Hiện đại</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="{{ asset('build/assets/welcome.css') }}?v={{ file_exists(public_path('build/assets/welcome.css')) ? filemtime(public_path('build/assets/welcome.css')) : time() }}" rel="stylesheet">
</head>
<body>
    <div id="welcome-app"></div>

    <script>
        window.welcomeData = {
            is_authenticated: @json(auth()->check()),
            user_name: @json(auth()->check() ? auth()->user()->name : ''),
            home_url: @json(route('home')),
            login_url: @json(route('login')),
            register_url: @json(route('register')),
            welcome_url: @json(route('welcome')),
        };
    </script>
    <script type="module" src="{{ asset('build/assets/welcome.js') }}?v={{ file_exists(public_path('build/assets/welcome.js')) ? filemtime(public_path('build/assets/welcome.js')) : time() }}"></script>
</body>
</html>
