<?php if(file_exists(public_path('build/assets/ai-assignee.css'))): ?>
<link href="<?php echo e(asset('build/assets/ai-assignee.css')); ?>?v=<?php echo e(filemtime(public_path('build/assets/ai-assignee.css'))); ?>" rel="stylesheet">
<?php endif; ?>
<script type="module" src="<?php echo e(asset('build/assets/ai-assignee-widget.js')); ?>?v=<?php echo e(file_exists(public_path('build/assets/ai-assignee-widget.js')) ? filemtime(public_path('build/assets/ai-assignee-widget.js')) : time()); ?>"></script>
<?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/partials/ai_assignee_assets.blade.php ENDPATH**/ ?>