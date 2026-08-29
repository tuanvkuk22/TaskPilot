<template>
    <div class="tp-stat-card">
        <div>
            <div class="tp-stat-label">{{ label }}</div>
            <div class="tp-stat-value">{{ value }}</div>
        </div>
        <div :class="['tp-stat-icon', variant]">
            <i :class="icon"></i>
        </div>
    </div>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [Number, String], default: 0 },
    icon: { type: String, default: 'fa fa-list-alt' },
    variant: { type: String, default: 'total' }, // total, pending, in_progress, done, urgent
});
</script>
