<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'TaskPilot')); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo e(asset('css/app.css')); ?>?v=<?php echo e(filemtime(public_path('css/app.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/design-tokens.css')); ?>?v=<?php echo e(filemtime(public_path('css/design-tokens.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/custom-ui.css')); ?>?v=<?php echo e(filemtime(public_path('css/custom-ui.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/project-ui.css')); ?>?v=<?php echo e(filemtime(public_path('css/project-ui.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/project_roles.css')); ?>?v=<?php echo e(filemtime(public_path('css/project_roles.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/review-ui.css')); ?>?v=<?php echo e(filemtime(public_path('css/review-ui.css'))); ?>" rel="stylesheet">
    <?php echo $__env->yieldPushContent('styles'); ?>
    <link href="<?php echo e(asset('css/dark-mode.css')); ?>?v=<?php echo e(filemtime(public_path('css/dark-mode.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/mobile-workspace.css')); ?>?v=<?php echo e(filemtime(public_path('css/mobile-workspace.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/ai-batch-polish.css')); ?>?v=<?php echo e(filemtime(public_path('css/ai-batch-polish.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/design-system.css')); ?>?v=<?php echo e(filemtime(public_path('css/design-system.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/team-ui.css')); ?>?v=<?php echo e(filemtime(public_path('css/team-ui.css'))); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/meeting-ui.css')); ?>?v=<?php echo e(filemtime(public_path('css/meeting-ui.css'))); ?>" rel="stylesheet">
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
                <?php if(auth()->guard()->check()): ?>
                    <button type="button" id="theme-toggle-mobile" class="tp-theme-toggle tp-theme-toggle--mobile" aria-label="Chuyển sang chế độ tối" aria-pressed="false" title="Chuyển sang chế độ tối">
                        <i class="fa fa-moon-o" aria-hidden="true"></i>
                        <span class="sr-only">Chuyển chế độ sáng/tối</span>
                    </button>
                <?php endif; ?>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
                    <span class="sr-only">Mở điều hướng</span>
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo e(auth()->check() ? route('home') : route('welcome')); ?>">
                    <span class="tp-brand-mark"><i class="fa fa-check"></i></span> TaskPilot
                </a>
            </div>
            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <?php if(auth()->guard()->check()): ?>
                    <ul class="nav navbar-nav">
                        <li class="<?php echo e(request()->routeIs('home') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('home')); ?>"><i class="fa fa-th-large nav-icon"></i> Tổng quan</a>
                        </li>
                        <li class="<?php echo e(request()->routeIs('projects.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('projects.index')); ?>"><i class="fa fa-folder nav-icon"></i> Dự án</a>
                        </li>
                        <li class="<?php echo e(request()->routeIs('tasks.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('tasks.index')); ?>"><i class="fa fa-tasks nav-icon"></i> Công việc</a>
                        </li>
                        <li class="<?php echo e(request()->routeIs('teams.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('teams.index')); ?>"><i class="fa fa-users nav-icon"></i> Đội nhóm</a>
                        </li>
                        <li class="<?php echo e(request()->routeIs('companies.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('companies.index')); ?>"><i class="fa fa-building nav-icon"></i> Khách hàng</a>
                        </li>
                        <li class="<?php echo e(request()->routeIs('skills.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('skills.index')); ?>" class="tp-nav-skills-badge">
                                <i class="fa fa-magic nav-icon-ai"></i> Hồ sơ kỹ năng
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
                <ul class="nav navbar-nav navbar-right">
                    <?php if(auth()->guard()->guest()): ?>
                        <li><a href="<?php echo e(route('login')); ?>"><i class="fa fa-sign-in"></i> Đăng nhập</a></li>
                        <li><a href="<?php echo e(route('register')); ?>"><i class="fa fa-user-plus"></i> Đăng ký</a></li>
                    <?php else: ?>
                        <li class="<?php echo e(request()->routeIs('guide') ? 'active' : ''); ?>"><a href="<?php echo e(route('guide')); ?>" title="Hướng dẫn sử dụng"><i class="fa fa-question-circle"></i> Hướng dẫn</a></li>
                        <?php echo $__env->make('partials.notification_bell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <li class="tp-theme-switcher">
                            <button type="button" id="theme-toggle-header" class="tp-theme-toggle" aria-label="Chuyển sang chế độ tối" aria-pressed="false" title="Chuyển sang chế độ tối">
                                <i class="fa fa-moon-o" aria-hidden="true"></i>
                                <span class="sr-only">Chuyển chế độ sáng/tối</span>
                            </button>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle tp-user-dropdown-btn" data-toggle="dropdown" role="button" aria-expanded="false">
                                <span class="tp-user-avatar"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></span>
                                <span class="tp-user-name"><?php echo e(auth()->user()->name); ?></span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right tp-dropdown-menu" role="menu">
                                <li class="dropdown-header">Hồ sơ & Hệ thống</li>
                                <li><a href="<?php echo e(route('skills.index')); ?>"><i class="fa fa-sliders"></i> Hồ sơ kỹ năng AI</a></li>
                                <li><a href="<?php echo e(route('guide')); ?>"><i class="fa fa-book"></i> Hướng dẫn sử dụng</a></li>
                                <li><a href="#" id="theme-toggle"><i class="fa fa-moon-o"></i> <span data-theme-label>Chế độ tối</span></a></li>
                                <li class="divider"></li>
                                <li><a href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger"><i class="fa fa-power-off"></i> Đăng xuất</a></li>
                            </ul>
                            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display:none"><?php echo csrf_field(); ?></form>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="tp-main">
      <div class="container">
        <?php echo $__env->make('partials.errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('partials.success', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="row"><?php echo $__env->yieldContent('content'); ?></div>
      </div>
    </main>
</div>
    <?php echo $__env->yieldContent('jqueryScript'); ?>
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
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/layouts/app.blade.php ENDPATH**/ ?>