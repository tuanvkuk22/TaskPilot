<!doctype html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'TaskPilot')); ?> — Workspace Quản lý Dự án & Tiến độ Hiện đại</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="<?php echo e(asset('build/assets/welcome.css')); ?>?v=<?php echo e(file_exists(public_path('build/assets/welcome.css')) ? filemtime(public_path('build/assets/welcome.css')) : time()); ?>" rel="stylesheet">
</head>
<body>
    <div id="welcome-app"></div>

    <script>
        window.welcomeData = {
            is_authenticated: <?php echo json_encode(auth()->check(), 15, 512) ?>,
            user_name: <?php echo json_encode(auth()->check() ? auth()->user()->name : '', 15, 512) ?>,
            home_url: <?php echo json_encode(route('home'), 15, 512) ?>,
            login_url: <?php echo json_encode(route('login'), 15, 512) ?>,
            register_url: <?php echo json_encode(route('register'), 15, 512) ?>,
            welcome_url: <?php echo json_encode(route('welcome'), 15, 512) ?>,
        };
    </script>
    <script type="module" src="<?php echo e(asset('build/assets/welcome.js')); ?>?v=<?php echo e(file_exists(public_path('build/assets/welcome.js')) ? filemtime(public_path('build/assets/welcome.js')) : time()); ?>"></script>
</body>
</html>
<?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/welcome.blade.php ENDPATH**/ ?>