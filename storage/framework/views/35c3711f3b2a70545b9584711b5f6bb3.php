<?php $__env->startSection('content'); ?>
<link href="<?php echo e(asset('build/assets/project-list.css')); ?>?v=<?php echo e(file_exists(public_path('build/assets/project-list.css')) ? filemtime(public_path('build/assets/project-list.css')) : time()); ?>" rel="stylesheet">
<div id="project-list-app"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('jqueryScript'); ?>
<script>
    window.projectListData = {
        projects: <?php echo json_encode($projects, 15, 512) ?>,
        stats: <?php echo json_encode($stats, 15, 512) ?>,
    };
</script>
<script type="module" src="<?php echo e(asset('build/assets/project-list.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/projects/index.blade.php ENDPATH**/ ?>