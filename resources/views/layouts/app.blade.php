<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TaskPilot') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/custom-ui.css') }}?v={{ filemtime(public_path('css/custom-ui.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/sidebar-layout.css') }}?v={{ file_exists(public_path('css/sidebar-layout.css')) ? filemtime(public_path('css/sidebar-layout.css')) : time() }}" rel="stylesheet">
    <link href="{{ asset('css/project-ui.css') }}?v={{ filemtime(public_path('css/project-ui.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/project_roles.css') }}?v={{ filemtime(public_path('css/project_roles.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/design-tokens.css') }}?v={{ file_exists(public_path('css/design-tokens.css')) ? filemtime(public_path('css/design-tokens.css')) : time() }}" rel="stylesheet">
    <link href="{{ asset('css/design-system.css') }}?v={{ file_exists(public_path('css/design-system.css')) ? filemtime(public_path('css/design-system.css')) : time() }}" rel="stylesheet">
    <link href="{{ asset('css/dark-mode.css') }}?v={{ file_exists(public_path('css/dark-mode.css')) ? filemtime(public_path('css/dark-mode.css')) : time() }}" rel="stylesheet">
    @stack('styles')
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
    <script src="{{ asset('js/sidebar-layout.js') }}?v={{ file_exists(public_path('js/sidebar-layout.js')) ? filemtime(public_path('js/sidebar-layout.js')) : time() }}" defer></script>
    <script>(function(){try{var t=localStorage.getItem('taskpilot-theme');if(t==='dark'||(!t&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('tp-theme-dark')}catch(e){}})();</script>
</head>
<body>
<div id="app">
    @auth
        <div class="tp-app-wrapper" id="tp-app-root">
            <!-- Mobile Backdrop -->
            <div class="tp-sidebar-backdrop" id="tp-sidebar-backdrop"></div>

            <!-- Vertical Sidebar (Thanh điều hướng dọc) -->
            <aside class="tp-sidebar" id="tp-sidebar">
                <!-- Sidebar Header: Logo / Brand -->
                <div class="tp-sidebar-header">
                    <a class="tp-sidebar-brand" href="{{ route('home') }}">
                        <span class="tp-brand-logo"><i class="fa fa-check"></i></span>
                        <span class="tp-brand-text">TaskPilot</span>
                    </a>
                    <button type="button" class="tp-mobile-close-btn" id="tp-mobile-close" aria-label="Đóng menu">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Sidebar Body: Navigation Links -->
                <div class="tp-sidebar-body">
                    <div class="tp-sidebar-group">
                        <div class="tp-sidebar-heading">Làm việc</div>
                        <a href="{{ route('home') }}" class="tp-sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}" data-title="Tổng quan">
                            <i class="fa fa-th-large tp-link-icon"></i>
                            <span class="tp-link-text">Tổng quan</span>
                        </a>
                        <a href="{{ route('projects.index') }}" class="tp-sidebar-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" data-title="Dự án">
                            <i class="fa fa-folder tp-link-icon"></i>
                            <span class="tp-link-text">Dự án</span>
                        </a>
                        <a href="{{ route('tasks.index') }}" class="tp-sidebar-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}" data-title="Công việc">
                            <i class="fa fa-tasks tp-link-icon"></i>
                            <span class="tp-link-text">Công việc</span>
                        </a>
                        <a href="{{ route('workflows.index') }}" class="tp-sidebar-link {{ request()->routeIs('workflows.*') ? 'active' : '' }}" data-title="Quy trình">
                            <i class="fa fa-sitemap tp-link-icon"></i>
                            <span class="tp-link-text">Quy trình</span>
                        </a>
                        <a href="{{ route('teams.index') }}" class="tp-sidebar-link {{ request()->routeIs('teams.*') ? 'active' : '' }}" data-title="Đội nhóm">
                            <i class="fa fa-users tp-link-icon"></i>
                            <span class="tp-link-text">Đội nhóm</span>
                        </a>
                        <a href="{{ route('companies.index') }}" class="tp-sidebar-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" data-title="Khách hàng">
                            <i class="fa fa-building tp-link-icon"></i>
                            <span class="tp-link-text">Khách hàng</span>
                        </a>
                    </div>

                    <div class="tp-sidebar-group">
                        <div class="tp-sidebar-heading">Hệ thống</div>
                        <a href="{{ route('guide') }}" class="tp-sidebar-link {{ request()->routeIs('guide') ? 'active' : '' }}" data-title="Hướng dẫn sử dụng">
                            <i class="fa fa-question-circle tp-link-icon"></i>
                            <span class="tp-link-text">Hướng dẫn sử dụng</span>
                        </a>
                        <a href="{{ route('profile.index') }}" class="tp-sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" data-title="Hồ sơ cá nhân">
                            <i class="fa fa-user tp-link-icon"></i>
                            <span class="tp-link-text">Hồ sơ cá nhân</span>
                        </a>
                    </div>
                </div>

                <!-- Sidebar Footer: Collapse Toggle Button -->
                <div class="tp-sidebar-footer">
                    <button type="button" class="tp-collapse-toggle-btn" id="tp-sidebar-collapse" title="Thu gọn / Mở rộng menu">
                        <i class="fa fa-angle-double-left"></i>
                        <span class="tp-collapse-text">Thu gọn menu</span>
                    </button>
                </div>
            </aside>

            <!-- Main Content Container -->
            <div class="tp-main-container">
                <!-- Topbar Header (Chứa ô tìm kiếm toàn cục & tác vụ nhanh) -->
                <header class="tp-topbar">
                    <div class="tp-topbar-left">
                        <button type="button" class="tp-mobile-menu-btn" id="tp-mobile-toggle" aria-label="Mở menu">
                            <i class="fa fa-bars"></i>
                        </button>

                        <!-- Global Search Bar -->
                        <div class="tp-search-container" id="tp-search-container">
                            <div class="tp-search-input-wrap">
                                <i class="fa fa-search tp-search-icon"></i>
                                <input type="text" id="tp-global-search-input" class="tp-search-input" placeholder="Tìm kiếm dự án, công việc... (Ctrl + K)" autocomplete="off">
                                <span class="tp-search-shortcut">Ctrl K</span>
                            </div>
                            <!-- Live search dropdown -->
                            <div class="tp-search-dropdown" id="tp-search-dropdown"></div>
                        </div>
                    </div>

                    <div class="tp-topbar-right">
                        <!-- Quick Create Task Action -->
                        <a href="{{ route('tasks.create') }}" class="tp-quick-btn tp-btn-primary" title="Tạo công việc mới">
                            <i class="fa fa-plus"></i>
                            <span>Tạo mới</span>
                        </a>

                        <!-- Dark Mode Toggle Button -->
                        <button type="button" class="tp-topbar-action-item tp-theme-toggle-btn" id="theme-toggle-header" aria-label="Chuyển chế độ tối/sáng" title="Chuyển chế độ tối/sáng">
                            <i class="fa fa-moon-o"></i>
                        </button>

                        <!-- Notification Bell Component -->
                        @include('partials.notification_bell')

                        <!-- User Profile Dropdown -->
                        <div class="tp-user-menu" id="tp-user-menu">
                            <button type="button" class="tp-user-btn" id="tp-user-dropdown-btn">
                                <span class="tp-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                <span class="tp-user-name">{{ auth()->user()->name }}</span>
                                <i class="fa fa-angle-down tp-user-caret"></i>
                            </button>
                            <div class="tp-user-dropdown-menu" id="tp-user-dropdown-menu">
                                <div class="tp-dropdown-user-header">
                                    <div class="tp-dropdown-user-name">{{ auth()->user()->name }}</div>
                                    <div class="tp-dropdown-user-email">{{ auth()->user()->email }}</div>
                                </div>
                                <a href="{{ route('profile.index') }}" class="tp-dropdown-item">
                                    <i class="fa fa-user"></i> <span>Hồ sơ cá nhân</span>
                                </a>
                                <a href="{{ route('guide') }}" class="tp-dropdown-item">
                                    <i class="fa fa-book"></i> <span>Hướng dẫn sử dụng</span>
                                </a>
                                <div style="height: 1px; background: #f1f5f9; margin: 4px 0;"></div>
                                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="tp-dropdown-item text-danger">
                                    <i class="fa fa-power-off"></i> <span>Đăng xuất</span>
                                </a>
                            </div>
                        </div>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
                    </div>
                </header>

                <!-- Page Main Body -->
                <main class="tp-main-content">
                    <div class="tp-content-body container-fluid">
                        @include('partials.errors')
                        @include('partials.success')
                        <div class="row">@yield('content')</div>
                    </div>
                </main>
            </div>
        </div>
    @else
        <!-- Guest Top Navigation Bar -->
        <nav class="navbar navbar-default navbar-static-top tp-navbar" role="navigation" style="margin-bottom: 0;">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
                        <span class="sr-only">Mở điều hướng</span>
                        <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{{ route('welcome') }}">
                        <span class="tp-brand-mark"><i class="fa fa-check"></i></span> TaskPilot
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="{{ route('login') }}"><i class="fa fa-sign-in"></i> Đăng nhập</a></li>
                        <li><a href="{{ route('register') }}"><i class="fa fa-user-plus"></i> Đăng ký</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <main class="tp-main" style="padding-top: 30px;">
            <div class="container">
                @include('partials.errors')
                @include('partials.success')
                <div class="row">@yield('content')</div>
            </div>
        </main>
    @endauth
</div>

@yield('jqueryScript')
<script>
(function() {
    // Theme toggle handling
    var themeToggles = document.querySelectorAll('#theme-toggle-header, .tp-theme-toggle');
    function updateThemeControls() {
        var isDark = document.documentElement.classList.contains('tp-theme-dark');
        themeToggles.forEach(function(toggle) {
            var icon = toggle.querySelector('i.fa');
            var action = isDark ? 'Chuyển sang chế độ sáng' : 'Chuyển sang chế độ tối';
            toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            toggle.setAttribute('aria-label', action);
            toggle.setAttribute('title', action);
            if (icon) {
                icon.className = isDark ? 'fa fa-sun-o' : 'fa fa-moon-o';
            }
        });
    }

    themeToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            document.documentElement.classList.toggle('tp-theme-dark');
            try {
                localStorage.setItem('taskpilot-theme', document.documentElement.classList.contains('tp-theme-dark') ? 'dark' : 'light');
            } catch (err) {}
            updateThemeControls();
        });
    });

    updateThemeControls();

    // Bootstrap dropdown fallback
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
