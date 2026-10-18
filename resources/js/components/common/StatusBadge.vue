<template>
    <span
        :class="['tp-badge', badgeClass]"
        :style="customStyle"
    >
        <i :class="badgeIcon" :style="customIconStyle"></i>
        <span>{{ formattedLabel }}</span>
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, default: 'todo' },
    customLabel: { type: String, default: '' },
    customColor: { type: String, default: '' },
    customIcon: { type: String, default: '' },
    workflowStatuses: { type: Array, default: () => [] },
});

const matchedStatus = computed(() => {
    if (!props.status) return null;
    if (props.workflowStatuses && props.workflowStatuses.length > 0) {
        return props.workflowStatuses.find(s => (s.key || s.value) === props.status);
    }
    return null;
});

const formattedLabel = computed(() => {
    if (props.customLabel) return props.customLabel;
    if (matchedStatus.value?.name || matchedStatus.value?.label) {
        return matchedStatus.value.name || matchedStatus.value.label;
    }
    const s = (props.status || '').toLowerCase();
    if (['done', 'completed', 'resolved', 'closed'].includes(s)) return 'Hoàn thành';
    if (['in_progress', 'doing'].includes(s)) return 'Đang làm';
    if (['review', 'in_review', 'testing', 'qa'].includes(s)) return 'Đang duyệt / QA';
    if (['blocked', 'cancelled'].includes(s)) return 'Bị chặn / Hủy';
    if (['todo', 'backlog', 'open', 'new', 'draft', 'application'].includes(s)) return 'Cần làm';
    return s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
});

const badgeIcon = computed(() => {
    if (props.customIcon) return props.customIcon.startsWith('fa') ? props.customIcon : `fa ${props.customIcon}`;
    if (matchedStatus.value?.icon) {
        const ic = matchedStatus.value.icon;
        return ic.startsWith('fa') ? ic : `fa ${ic}`;
    }
    const s = (props.status || '').toLowerCase();
    if (['done', 'completed', 'resolved', 'closed'].includes(s)) return 'fa fa-check-circle';
    if (['in_progress', 'doing'].includes(s)) return 'fa fa-spinner';
    if (['review', 'in_review', 'testing', 'qa'].includes(s)) return 'fa fa-eye';
    if (['blocked', 'cancelled'].includes(s)) return 'fa fa-ban';
    return 'fa fa-circle-o';
});

const badgeColor = computed(() => {
    return props.customColor || matchedStatus.value?.color || '';
});

const customStyle = computed(() => {
    if (badgeColor.value) {
        return {
            backgroundColor: `${badgeColor.value}18`,
            color: badgeColor.value,
            borderColor: `${badgeColor.value}40`,
            borderWidth: '1px',
            borderStyle: 'solid',
        };
    }
    return {};
});

const customIconStyle = computed(() => {
    if (badgeColor.value) {
        return { color: badgeColor.value };
    }
    return {};
});

const badgeClass = computed(() => {
    if (badgeColor.value) return '';
    const s = (props.status || '').toLowerCase();
    if (['done', 'completed', 'resolved', 'closed'].includes(s)) return 'tp-badge-status-done';
    if (['in_progress', 'doing'].includes(s)) return 'tp-badge-status-in_progress';
    if (['review', 'in_review', 'testing', 'qa'].includes(s)) return 'tp-badge-status-review';
    return 'tp-badge-status-todo';
});
</script>
