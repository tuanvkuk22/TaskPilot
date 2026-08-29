<template>
    <div class="tp-empty-state">
        <div class="tp-empty-icon">
            <i :class="icon"></i>
        </div>
        <h3>{{ title }}</h3>
        <p v-if="description">{{ description }}</p>
        <div style="display: inline-flex; gap: 12px; align-items: center; justify-content: center;">
            <slot name="actions"></slot>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    icon: { type: String, default: 'fa fa-inbox' },
});
</script>
