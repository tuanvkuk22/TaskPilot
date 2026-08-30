<template>
    <div v-if="name" class="user-pill" :title="name" :class="{ 'avatar-only-mode': onlyAvatar }">
        <div class="user-avatar-sm">{{ initials }}</div>
        <span v-if="!onlyAvatar">{{ name }}</span>
    </div>
    <span v-else class="text-muted" style="font-size: 12px; font-style: italic;">{{ unassignedLabel }}</span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, default: '' },
    unassignedLabel: { type: String, default: 'Chưa phân công' },
    onlyAvatar: { type: Boolean, default: false },
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

<style scoped>
.user-pill.avatar-only-mode {
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.user-avatar-sm {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #2563eb;
    color: #ffffff !important;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
}
</style>

