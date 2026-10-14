<?php $__env->startSection('content'); ?>
<link href="<?php echo e(asset('build/assets/task-shared.css')); ?>?v=<?php echo e(file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time()); ?>" rel="stylesheet">
<div id="task-index-app" class="task-index-container"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('jqueryScript'); ?>
<script>
    window.taskListData = {
        tasks: <?php echo json_encode($tasks ?? [], 15, 512) ?>,
        projects: <?php echo json_encode($projects ?? [], 15, 512) ?>,
        stats: <?php echo json_encode($stats ?? [], 15, 512) ?>,
        create_task_url: <?php echo json_encode(route('tasks.create'), 15, 512) ?>,
        selected_project: <?php echo json_encode($selectedProject ?? '', 15, 512) ?>,
        selected_status: <?php echo json_encode($selectedStatus ?? '', 15, 512) ?>,
        selected_priority: <?php echo json_encode($selectedPriority ?? '', 15, 512) ?>,
        success_message: <?php echo json_encode(session('task_success'), 15, 512) ?>,
    };
</script>
<script type="module" src="<?php echo e(asset('build/assets/task-index.js')); ?>?v=<?php echo e(file_exists(public_path('build/assets/task-index.js')) ? filemtime(public_path('build/assets/task-index.js')) : time()); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/tasks/index.blade.php ENDPATH**/ ?>