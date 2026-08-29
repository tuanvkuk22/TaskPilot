<template>
    <span :class="['tp-badge', badgeClass]">
        <i :class="badgeIcon"></i>
        <span>{{ formattedLabel }}</span>
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: { type: String, default: 'task' },
});

const formattedLabel = computed(() => {
    const map = { task: 'Task', bug: 'Bug', story: 'Story', epic: 'Epic', subtask: 'Sub-task' };
    return map[props.type] || 'Task';
});

const badgeIcon = computed(() => {
    const map = {
        task: 'fa fa-check-square-o',
        bug: 'fa fa-bug',
        story: 'fa fa-bookmark',
        epic: 'fa fa-bolt',
        subtask: 'fa fa-code-fork'
    };
    return map[props.type] || 'fa fa-check-square-o';
});

const badgeClass = computed(() => {
    return `tp-badge-type-${props.type || 'task'}`;
});
</script>
