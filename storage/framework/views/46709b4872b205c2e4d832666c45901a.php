<?php $__env->startSection('content'); ?>
<div id="login-app"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-style'); ?>
<link href="<?php echo e(asset('build/assets/login.css')); ?>?v=<?php echo e(file_exists(public_path('build/assets/login.css')) ? filemtime(public_path('build/assets/login.css')) : time()); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('auth-scripts'); ?>
<script>
    window.loginData = {
        csrf_token: <?php echo json_encode(csrf_token(), 15, 512) ?>,
        errors: <?php echo json_encode($errors->all(), 15, 512) ?>,
        old: <?php echo json_encode(old(), 15, 512) ?>,
        login_url: <?php echo json_encode(route('login'), 15, 512) ?>,
        google_redirect_url: <?php echo json_encode(route('google.redirect'), 15, 512) ?>,
        password_reset_url: <?php echo json_encode(route('password.request'), 15, 512) ?>,
        register_url: <?php echo json_encode(route('register'), 15, 512) ?>,
        status: <?php echo json_encode(session('status'), 15, 512) ?>,
    };
</script>
<script type="module" src="<?php echo e(asset('build/assets/login.js')); ?>?v=<?php echo e(file_exists(public_path('build/assets/login.js')) ? filemtime(public_path('build/assets/login.js')) : time()); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/auth/login.blade.php ENDPATH**/ ?>