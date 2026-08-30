<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TaskPilot') }} — @yield('title', 'Đăng nhập')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --accent-purple: #8b5cf6;
            --dark-bg: #0b0f19;
            --dark-surface: #111827;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: radial-gradient(1200px 600px at 50% -10%, #4f46e5 0%, #0b0f19 60%, #0b0f19 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        i.fa, i.fas, i.far, i.fab, i.fal, i.fad, .fa, .fas, .far, .fab, .fal, .fad, [class^="fa-"], [class*=" fa-"] {
            font-family: FontAwesome, 'Font Awesome 5 Free', 'Font Awesome 5 Brands', 'Font Awesome 6 Free', 'Font Awesome 6 Brands' !important;
        }
    </style>

    @yield('page-style')
</head>
<body>
    @yield('content')

    @yield('auth-scripts')
</body>
</html>
