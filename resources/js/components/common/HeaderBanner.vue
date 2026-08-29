<template>
    <div class="tp-header-banner">
        <div class="tp-header-content">
            <div v-if="eyebrow" class="tp-eyebrow">
                <i :class="eyebrowIcon"></i> {{ eyebrow }}
            </div>
            <h1>{{ title }}</h1>
            <p v-if="description">{{ description }}</p>
            <slot name="subtitle"></slot>
        </div>
        <div class="tp-header-actions">
            <slot name="actions"></slot>
        </div>
    </div>
</template>

<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    eyebrowIcon: { type: String, default: 'fa fa-tasks' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>
