<?php $__env->startPush('styles'); ?>
<?php if(file_exists(public_path('build/assets/home.css'))): ?>
    <link href="<?php echo e(asset('build/assets/home.css')); ?>?v=<?php echo e(filemtime(public_path('build/assets/home.css'))); ?>" rel="stylesheet">
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="col-xs-12">
    <div id="home-app"></div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
window.homeData = {
    user_name: <?php echo json_encode(auth()->user()->name, 15, 512) ?>,
    total_projects: <?php echo json_encode($totalProjects, 15, 512) ?>,
    total_tasks: <?php echo json_encode($totalTasks, 15, 512) ?>,
    total_companies: <?php echo json_encode($totalCompanies, 15, 512) ?>,
    user_tasks: <?php echo json_encode($userTasks, 15, 512) ?>,
    unassigned_tasks: <?php echo json_encode($unassignedTasks, 15, 512) ?>,
    routes: {
        tasksCreate: <?php echo json_encode(route('tasks.create'), 15, 512) ?>,
        projectsCreate: <?php echo json_encode(route('projects.create'), 15, 512) ?>,
        projectsIndex: <?php echo json_encode(route('projects.index'), 15, 512) ?>,
        tasksIndex: <?php echo json_encode(route('tasks.index'), 15, 512) ?>,
        teamsIndex: <?php echo json_encode(route('teams.index'), 15, 512) ?>,
        companiesIndex: <?php echo json_encode(route('companies.index'), 15, 512) ?>,
        skillsIndex: <?php echo json_encode(route('skills.index'), 15, 512) ?>,
        guide: <?php echo json_encode(route('guide'), 15, 512) ?>
    }
};
</script>
<?php if(file_exists(public_path('build/assets/home.js'))): ?>
    <script type="module" src="<?php echo e(asset('build/assets/home.js')); ?>?v=<?php echo e(filemtime(public_path('build/assets/home.js'))); ?>"></script>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('jqueryScript'); ?>
<?php echo $__env->make('partials.ai_assignee_assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/home.blade.php ENDPATH**/ ?>