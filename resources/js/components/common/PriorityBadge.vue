<template>
    <span :class="['tp-badge', badgeClass]">
        <i :class="badgeIcon"></i>
        <span>{{ formattedLabel }}</span>
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    priority: { type: String, default: 'medium' },
});

const formattedLabel = computed(() => {
    const map = { low: 'Thấp', medium: 'Trung bình', high: 'Cao', urgent: 'Rất gấp' };
    return map[props.priority] || 'Trung bình';
});

const badgeIcon = computed(() => {
    if (props.priority === 'low') return 'fa fa-arrow-down';
    if (props.priority === 'high' || props.priority === 'urgent') return 'fa fa-arrow-up';
    return 'fa fa-minus';
});

const badgeClass = computed(() => {
    if (props.priority === 'low') return 'tp-badge-priority-low';
    if (props.priority === 'high' || props.priority === 'urgent') return 'tp-badge-priority-high';
    return 'tp-badge-priority-medium';
});
</script>
