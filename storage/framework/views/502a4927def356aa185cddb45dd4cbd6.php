<?php $__env->startSection('content'); ?>
<link href="<?php echo e(asset('build/assets/task-shared.css')); ?>?v=<?php echo e(file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time()); ?>" rel="stylesheet">
<?php if(file_exists(public_path('build/assets/project-dashboard.css'))): ?>
<link href="<?php echo e(asset('build/assets/project-dashboard.css')); ?>?v=<?php echo e(filemtime(public_path('build/assets/project-dashboard.css'))); ?>" rel="stylesheet">
<?php endif; ?>

<div id="project-dashboard-app"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('jqueryScript'); ?>
<script>
    window.projectDashboardData = {
        project: <?php echo json_encode($project, 15, 512) ?>,
        tasks: <?php echo json_encode($project->tasks, 15, 512) ?>,
        comments: <?php echo json_encode($comments, 15, 512) ?>,
        activities: <?php echo json_encode($activities, 15, 512) ?>,
    };
</script>
<script type="module" src="<?php echo e(asset('build/assets/project-dashboard.js')); ?>?v=<?php echo e(file_exists(public_path('build/assets/project-dashboard.js')) ? filemtime(public_path('build/assets/project-dashboard.js')) : time()); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/projects/show.blade.php ENDPATH**/ ?>