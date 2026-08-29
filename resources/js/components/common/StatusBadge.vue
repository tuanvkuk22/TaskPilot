<template>
    <span :class="['tp-badge', badgeClass]">
        <i :class="badgeIcon"></i>
        <span>{{ formattedLabel }}</span>
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, default: 'todo' },
});

const formattedLabel = computed(() => {
    if (['done', 'completed', 'resolved', 'closed'].includes(props.status)) return 'Hoàn thành';
    if (['in_progress', 'doing', 'review', 'in_review'].includes(props.status)) return 'Đang làm';
    return 'Cần làm';
});

const badgeIcon = computed(() => {
    if (['done', 'completed', 'resolved', 'closed'].includes(props.status)) return 'fa fa-check-circle';
    if (['in_progress', 'doing', 'review', 'in_review'].includes(props.status)) return 'fa fa-spinner';
    return 'fa fa-circle-o';
});

const badgeClass = computed(() => {
    if (['done', 'completed', 'resolved', 'closed'].includes(props.status)) return 'tp-badge-status-done';
    if (['in_progress', 'doing', 'review', 'in_review'].includes(props.status)) return 'tp-badge-status-in_progress';
    return 'tp-badge-status-todo';
});
</script>
