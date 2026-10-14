<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TaskPilot') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/design-tokens.css') }}?v={{ filemtime(public_path('css/design-tokens.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/custom-ui.css') }}?v={{ filemtime(public_path('css/custom-ui.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/project-ui.css') }}?v={{ filemtime(public_path('css/project-ui.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/project_roles.css') }}?v={{ filemtime(public_path('css/project_roles.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/review-ui.css') }}?v={{ filemtime(public_path('css/review-ui.css')) }}" rel="stylesheet">
    @stack('styles')
    <link href="{{ asset('css/dark-mode.css') }}?v={{ filemtime(public_path('css/dark-mode.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/mobile-workspace.css') }}?v={{ filemtime(public_path('css/mobile-workspace.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/ai-batch-polish.css') }}?v={{ filemtime(public_path('css/ai-batch-polish.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/design-system.css') }}?v={{ filemtime(public_path('css/design-system.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/team-ui.css') }}?v={{ filemtime(public_path('css/team-ui.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/meeting-ui.css') }}?v={{ filemtime(public_path('css/meeting-ui.css')) }}" rel="stylesheet">
    <style>
        body, button, input, select, textarea, .navbar, .btn, h1, h2, h3, h4, h5, h6, label, p, span, a, div {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
        }
        /* Preserve FontAwesome Icon Glyphs */
        i.fa, i.fas, i.far, i.fab, i.fal, i.fad, .fa, .fas, .far, .fab, .fal, .fad, [class^="fa-"], [class*=" fa-"] {
            font-family: FontAwesome, 'Font Awesome 5 Free', 'Font Awesome 5 Brands' !important;
        }
    </style>
    <script src="https://use.fontawesome.com/874dbadbd7.js" defer></script>
    <script>(function(){try{var t=localStorage.getItem('taskpilot-theme');if(t==='dark'||(!t&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('tp-theme-dark')}catch(e){}})();</script>
</head>
<body>
<div id="app">
    <nav class="navbar navbar-default navbar-static-top tp-navbar" role="navigation">
        <div class="container">
            <div class="navbar-header">
                @auth
                    <button type="button" id="theme-toggle-mobile" class="tp-theme-toggle tp-theme-toggle--mobile" aria-label="Chuyển sang chế độ tối" aria-pressed="false" title="Chuyển sang chế độ tối">
                        <i class="fa fa-moon-o" aria-hidden="true"></i>
                        <span class="sr-only">Chuyển chế độ sáng/tối</span>
                    </button>
                @endauth
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
                    <span class="sr-only">Mở điều hướng</span>
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{ auth()->check() ? route('home') : route('welcome') }}">
                    <span class="tp-brand-mark"><i class="fa fa-check"></i></span> TaskPilot
                </a>
            </div>
            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                @auth
                    <ul class="nav navbar-nav">
                        <li class="{{ request()->routeIs('home') ? 'active' : '' }}">
                            <a href="{{ route('home') }}"><i class="fa fa-th-large nav-icon"></i> Tổng quan</a>
                        </li>
                        <li class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">
                            <a href="{{ route('projects.index') }}"><i class="fa fa-folder nav-icon"></i> Dự án</a>
                        </li>
                        <li class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                            <a href="{{ route('tasks.index') }}"><i class="fa fa-tasks nav-icon"></i> Công việc</a>
                        </li>
                        <li class="{{ request()->routeIs('teams.*') ? 'active' : '' }}">
                            <a href="{{ route('teams.index') }}"><i class="fa fa-users nav-icon"></i> Đội nhóm</a>
                        </li>
                        <li class="{{ request()->routeIs('companies.*') ? 'active' : '' }}">
                            <a href="{{ route('companies.index') }}"><i class="fa fa-building nav-icon"></i> Khách hàng</a>
                        </li>
                        <li class="{{ request()->routeIs('skills.*') ? 'active' : '' }}">
                            <a href="{{ route('skills.index') }}" class="tp-nav-skills-badge">
                                <i class="fa fa-magic nav-icon-ai"></i> Hồ sơ kỹ năng
                            </a>
                        </li>
                    </ul>
                @endauth
                <ul class="nav navbar-nav navbar-right">
                    @guest
                        <li><a href="{{ route('login') }}"><i class="fa fa-sign-in"></i> Đăng nhập</a></li>
                        <li><a href="{{ route('register') }}"><i class="fa fa-user-plus"></i> Đăng ký</a></li>
                    @else
                        <li class="{{ request()->routeIs('guide') ? 'active' : '' }}"><a href="{{ route('guide') }}" title="Hướng dẫn sử dụng"><i class="fa fa-question-circle"></i> Hướng dẫn</a></li>
                        @include('partials.notification_bell')
                        <li class="tp-theme-switcher">
                            <button type="button" id="theme-toggle-header" class="tp-theme-toggle" aria-label="Chuyển sang chế độ tối" aria-pressed="false" title="Chuyển sang chế độ tối">
                                <i class="fa fa-moon-o" aria-hidden="true"></i>
                                <span class="sr-only">Chuyển chế độ sáng/tối</span>
                            </button>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle tp-user-dropdown-btn" data-toggle="dropdown" role="button" aria-expanded="false">
                                <span class="tp-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                <span class="tp-user-name">{{ auth()->user()->name }}</span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right tp-dropdown-menu" role="menu">
                                <li class="dropdown-header">Hồ sơ & Hệ thống</li>
                                <li><a href="{{ route('skills.index') }}"><i class="fa fa-sliders"></i> Hồ sơ kỹ năng AI</a></li>
                                <li><a href="{{ route('guide') }}"><i class="fa fa-book"></i> Hướng dẫn sử dụng</a></li>
                                <li><a href="#" id="theme-toggle"><i class="fa fa-moon-o"></i> <span data-theme-label>Chế độ tối</span></a></li>
                                <li class="divider"></li>
                                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger"><i class="fa fa-power-off"></i> Đăng xuất</a></li>
                            </ul>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="tp-main">
      <div class="container">
        @include('partials.errors')
        @include('partials.success')
        <div class="row">@yield('content')</div>
      </div>
    </main>
</div>
    @yield('jqueryScript')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggles = document.querySelectorAll('#theme-toggle, #theme-toggle-header, #theme-toggle-mobile');
        if (!toggles.length) return;

        function updateThemeControls() {
            var isDark = document.documentElement.classList.contains('tp-theme-dark');
            toggles.forEach(function (toggle) {
                var icon = toggle.querySelector('i.fa');
                var label = toggle.querySelector('[data-theme-label]');
                var action = isDark ? 'Chuyển sang chế độ sáng' : 'Chuyển sang chế độ tối';

                toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                toggle.setAttribute('aria-label', action);
                toggle.setAttribute('title', action);
                if (icon) icon.className = isDark ? 'fa fa-sun-o' : 'fa fa-moon-o';
                if (label) label.textContent = isDark ? 'Chế độ sáng' : 'Chế độ tối';
            });
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                document.documentElement.classList.toggle('tp-theme-dark');
                try {
                    localStorage.setItem('taskpilot-theme', document.documentElement.classList.contains('tp-theme-dark') ? 'dark' : 'light');
                } catch (error) {}
                updateThemeControls();
            });
        });

        updateThemeControls();
    });
    </script>
    <script>
    (function() {
        if (typeof jQuery === 'undefined' && !window.bootstrap) {
            var toggles = document.querySelectorAll('[data-toggle="dropdown"]');
            toggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var menu = this.nextElementSibling;
                    if (menu && menu.classList.contains('dropdown-menu')) {
                        var isOpen = menu.style.display === 'block';
                        menu.style.display = isOpen ? 'none' : 'block';
                        if (!isOpen) {
                            var closeFn = function(ev) {
                                if (!menu.contains(ev.target) && !toggle.contains(ev.target)) {
                                    menu.style.display = 'none';
                                    document.removeEventListener('click', closeFn);
                                }
                            };
                            document.addEventListener('click', closeFn);
                        }
                    }
                });
            });
        }
    })();
    </script>
    @stack('scripts')
</body>
</html>
