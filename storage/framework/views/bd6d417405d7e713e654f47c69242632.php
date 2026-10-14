<?php $__env->startPush('styles'); ?>
<link href="<?php echo e(asset('css/sprint-review.css')); ?>?v=<?php echo e(filemtime(public_path('css/sprint-review.css'))); ?>" rel="stylesheet">
<link rel="stylesheet" href="<?php echo e(asset('build/assets/task-shared.css')); ?>?v=<?php echo e(file_exists(public_path('build/assets/task-shared.css')) ? filemtime(public_path('build/assets/task-shared.css')) : time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $doneStatuses = ['done', 'closed', 'completed', 'resolved'];
    $movableSprints = $project->sprints->whereIn('status', ['planned', 'active']);
?>
<div class="project-dashboard-vue sprint-project-page container-fluid py-3" style="max-width: 1400px;">
    <div class="pd-header mb-3">
        <div class="pd-header-left">
            <div class="pd-breadcrumb mb-1">
                <a href="<?php echo e(route('projects.index')); ?>">Dự án</a><span>/</span><span><?php echo e($project->name); ?></span><span>/</span><span class="text-dark font-weight-bold">Backlog / Sprint</span>
            </div>
            <div class="pd-title-row">
                <h1 class="pd-title m-0"><?php echo e($project->name); ?></h1>
                <span class="pd-project-key"><?php echo e($project->project_code ?? 'PROJ'); ?></span>
                <span class="pd-project-status"><?php echo e(strtoupper($project->status ?? 'active')); ?></span>
                <a href="<?php echo e(route('projects.edit', $project)); ?>" class="pd-settings-btn" title="Cài đặt dự án"><i class="fa fa-cog"></i></a>
            </div>
        </div>
        <a href="<?php echo e(route('tasks.create', ['project_id' => $project->id])); ?>" class="tp-btn tp-btn-primary"><i class="fa fa-plus-circle" style="color: #ffffff !important;"></i><span>Tạo công việc mới</span></a>
    </div>

    <div class="pd-project-nav mb-4">
        <a href="<?php echo e(route('projects.index')); ?>" class="pd-nav-item">Dự án</a>
        <a href="<?php echo e(route('projects.show', $project)); ?>" class="pd-nav-item">Tổng quan</a>
        <a href="<?php echo e(route('projects.board', $project)); ?>" class="pd-nav-item">Bảng</a>
        <a href="<?php echo e(route('projects.members.index', $project)); ?>" class="pd-nav-item">Thành viên</a>
        <a href="<?php echo e(route('projects.roles.index', $project)); ?>" class="pd-nav-item">Vai trò &amp; Quyền</a>
        <a href="<?php echo e(route('sprints.index', $project)); ?>" class="pd-nav-item active">Backlog / Sprint</a>
        <a href="<?php echo e(route('projects.timeline', $project)); ?>" class="pd-nav-item">Timeline</a>
        <a href="<?php echo e(route('projects.roadmap', $project)); ?>" class="pd-nav-item">Roadmap</a>
        <a href="<?php echo e(route('projects.reports.show', $project)); ?>" class="pd-nav-item">Báo cáo</a>
    </div>

    <section class="sprint-jira-toolbar" aria-label="Tổng quan Backlog và Sprint">
        <div class="sprint-jira-toolbar-title"><span class="sprint-jira-toolbar-icon"><i class="fa fa-list-alt"></i></span><div><h2>Backlog &amp; Sprint</h2><p>Lập kế hoạch, ưu tiên và theo dõi công việc theo từng Sprint.</p></div></div>
        <div class="sprint-jira-toolbar-stats"><span><strong><?php echo e($backlog->count()); ?></strong> Backlog</span><span><strong><?php echo e($project->sprints->where('status', 'active')->count()); ?></strong> Đang chạy</span></div>
    </section>

    <div class="jira-workspace sprint-workspace sprint-jira-inspired">
    <div class="jira-workspace-heading" aria-hidden="true" style="display: none;">
        <div><span class="jira-eyebrow"><?php echo e($project->project_code); ?></span><h1><?php echo e($project->name); ?> / Backlog & Sprint</h1><p>Lập kế hoạch Sprint, ưu tiên Backlog và theo dõi tiến độ công việc trong một nơi.</p></div>
        <div class="jira-view-links"><a href="<?php echo e(route('projects.board', $project)); ?>">Board</a><a class="active" href="<?php echo e(route('sprints.index', $project)); ?>">Backlog / Sprint</a><a href="<?php echo e(route('projects.roadmap', $project)); ?>">Roadmap</a><a href="<?php echo e(route('projects.reports.show', $project)); ?>">Báo cáo</a></div>
    </div>
    <div class="sprint-summary-grid">
        <div class="sprint-summary-card"><span>Backlog</span><strong><?php echo e($backlog->count()); ?></strong><small>công việc chưa lên Sprint</small></div>
        <div class="sprint-summary-card"><span>Sprint đang chạy</span><strong><?php echo e($project->sprints->where('status', 'active')->count()); ?></strong><small>chỉ một Sprint được kích hoạt</small></div>
        <div class="sprint-summary-card"><span>Tổng Sprint</span><strong><?php echo e($project->sprints->count()); ?></strong><small>đã lên kế hoạch và đã hoàn thành</small></div>
    </div>
    <div class="jira-sprint-layout">
        <section class="jira-sprint-main">
            <div class="jira-panel-heading"><div><h2>Backlog <span><?php echo e($backlog->count()); ?></span></h2><small>Chọn Sprint để đưa công việc vào kế hoạch, hoặc giữ lại trong Backlog.</small></div><a href="<?php echo e(route('tasks.create', ['project_id' => $project->id])); ?>" class="btn btn-primary btn-sm">+ Tạo issue</a></div>
            <div class="sprint-list-columns" aria-hidden="true"><span>Công việc</span><span>Sprint đích</span></div>
            <div class="jira-backlog-list sprint-task-list">
                <?php $__empty_1 = true; $__currentLoopData = $backlog; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="jira-backlog-item sprint-task-row"><a class="sprint-task-row__main" href="<?php echo e(route('tasks.show', $task)); ?>"><span class="jira-issue-key"><?php echo e($task->issue_key ?? 'TP-' . str_pad($task->id, 5, '0', STR_PAD_LEFT)); ?></span><span class="jira-issue-type jira-type-<?php echo e($task->issue_type); ?>"><?php echo e(strtoupper($task->issue_type ?? 'task')); ?></span><strong><?php echo e($task->name); ?></strong><small class="sprint-task-row__meta"><span><i class="fa fa-user-o"></i> <?php echo e($task->assignee?->name ?? 'Chưa phân công'); ?></span><span class="jira-status jira-status-<?php echo e(strtolower($task->status ?? 'todo')); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $task->status))); ?></span></small></a>
                    <?php if($movableSprints->isNotEmpty()): ?><form method="POST" action="<?php echo e(route('sprints.tasks.move', [$project, $task])); ?>" class="sprint-task-row__move"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><select name="sprint_id" aria-label="Chuyển task vào Sprint" onchange="this.form.submit()"><option value="">Đưa vào Sprint…</option><?php $__currentLoopData = $movableSprints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sprint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sprint->id); ?>"><?php echo e($sprint->name); ?> (<?php echo e($sprint->status === 'active' ? 'Đang chạy' : 'Kế hoạch'); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></form><?php endif; ?></article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <div class="jira-empty-column">Backlog đang trống. Tạo issue mới hoặc trả task từ một Sprint về đây.</div> <?php endif; ?>
            </div>
        </section>
        <aside class="jira-sprint-side"><div class="jira-panel-heading"><h2>Tạo Sprint</h2></div><form method="POST" action="<?php echo e(route('sprints.store', $project)); ?>" class="jira-sprint-form"><?php echo csrf_field(); ?><input name="name" class="form-control" placeholder="Tên Sprint" required><input name="goal" class="form-control" placeholder="Mục tiêu Sprint"><div class="jira-sprint-dates"><input type="date" name="starts_at" class="form-control"><input type="date" name="ends_at" class="form-control"></div><button class="btn btn-primary btn-sm">Tạo Sprint</button></form></aside>
    </div>
    <section class="sprint-board-section"><div class="jira-panel-heading"><div><h2>Sprint</h2><small>Tiến độ được tính từ công việc đã hoàn thành trong từng Sprint.</small></div></div><div class="sprint-board-grid">
        <?php $__empty_1 = true; $__currentLoopData = $project->sprints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sprint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $total = $sprint->tasks->count(); $done = $sprint->tasks->filter(fn ($task) => in_array(strtolower($task->status), $doneStatuses, true))->count(); $progress = $total ? (int) round(($done / $total) * 100) : 0; ?>
            <article class="jira-sprint-card sprint-board-card"><header><div><strong><?php echo e($sprint->name); ?></strong><span class="jira-status jira-status-<?php echo e($sprint->status); ?>"><?php echo e($sprint->status === 'active' ? 'Đang chạy' : ($sprint->status === 'planned' ? 'Kế hoạch' : 'Đã đóng')); ?></span></div><small><?php echo e($sprint->starts_at?->format('d/m') ?? 'Chưa đặt ngày'); ?> – <?php echo e($sprint->ends_at?->format('d/m/Y') ?? 'Chưa đặt ngày'); ?></small></header><p class="sprint-board-card__goal"><?php echo e($sprint->goal ?: 'Chưa có mục tiêu Sprint.'); ?></p><div class="sprint-progress-label"><span><?php echo e($done); ?>/<?php echo e($total); ?> đã hoàn thành</span><strong><?php echo e($progress); ?>%</strong></div><div class="sprint-progress" role="progressbar" aria-valuenow="<?php echo e($progress); ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?php echo e($progress); ?>%"></span></div><div class="jira-sprint-actions"><?php if($sprint->status === 'planned'): ?><form method="POST" action="<?php echo e(route('sprints.start', [$project, $sprint])); ?>"><?php echo csrf_field(); ?><button class="btn btn-outline-success btn-xs">Bắt đầu</button></form><?php endif; ?> <?php if($sprint->status === 'active'): ?><form method="POST" action="<?php echo e(route('sprints.close', [$project, $sprint])); ?>"><?php echo csrf_field(); ?><button class="btn btn-outline-secondary btn-xs">Đóng Sprint</button></form><?php endif; ?></div><div class="sprint-board-card__tasks">
                <?php $__empty_2 = true; $__currentLoopData = $sprint->tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><div class="sprint-task-row"><a class="sprint-task-row__main" href="<?php echo e(route('tasks.show', $task)); ?>"><span class="jira-issue-key"><?php echo e($task->issue_key ?? 'TP-' . str_pad($task->id, 5, '0', STR_PAD_LEFT)); ?></span><strong><?php echo e($task->name); ?></strong><small class="sprint-task-row__meta"><span><i class="fa fa-user-o"></i> <?php echo e($task->assignee?->name ?? 'Chưa phân công'); ?></span><span class="jira-status jira-status-<?php echo e(strtolower($task->status ?? 'todo')); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $task->status))); ?></span></small></a><form method="POST" action="<?php echo e(route('sprints.tasks.move', [$project, $task])); ?>" class="sprint-task-row__move"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><select name="sprint_id" aria-label="Chuyển task"><option value="<?php echo e($sprint->id); ?>"><?php echo e($sprint->name); ?></option><option value="">Trả về Backlog</option><?php $__currentLoopData = $movableSprints->where('id', '!=', $sprint->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $other): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($other->id); ?>"><?php echo e($other->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><button class="btn btn-link btn-xs">Chuyển</button></form></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?> <p class="sprint-empty">Chưa có công việc. Hãy đưa task từ Backlog vào Sprint này.</p> <?php endif; ?>
            </div></article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <div class="jira-empty-column">Chưa có Sprint. Tạo Sprint đầu tiên để bắt đầu kế hoạch.</div> <?php endif; ?>
    </div></section>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/sprints/index.blade.php ENDPATH**/ ?>