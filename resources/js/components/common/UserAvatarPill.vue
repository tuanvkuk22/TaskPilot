<template>
    <div v-if="name" class="user-pill">
        <div class="user-avatar-sm">{{ initials }}</div>
        <span>{{ name }}</span>
    </div>
    <span v-else class="text-muted" style="font-size: 12px; font-style: italic;">{{ unassignedLabel }}</span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, default: '' },
    unassignedLabel: { type: String, default: 'Chưa phân công' },
});

const initials = computed(() => {
    if (!props.name) return 'U';
    const parts = props.name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return props.name.substring(0, 2).toUpperCase();
});
</script>
