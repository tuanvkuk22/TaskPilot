<?php if(session()->has('success')): ?>
    <div class="alert alert-dismissable alert-success">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>
            <?php echo session()->get('success'); ?>

        </strong>
    </div>
<?php endif; ?>

<?php /**PATH /home/tuantn1807/project/personal_project/TaskPilot-v2-2026-10-04/resources/views/partials/success.blade.php ENDPATH**/ ?>