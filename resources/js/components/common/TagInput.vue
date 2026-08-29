<template>
    <div class="tag-input-container" ref="containerRef" @click="focusInput">
        <div class="tag-list">
            <span v-for="(tag, index) in modelValue" :key="index" class="tag-item">
                #{{ tag }}
                <button type="button" class="tag-remove" @click.stop="removeTag(index)">
                    <i class="fa fa-times"></i>
                </button>
            </span>
        </div>
        <input
            type="text"
            class="tag-input"
            ref="inputRef"
            :placeholder="placeholder"
            @keydown="handleKeydown"
            @blur="handleBlur"
        >
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Nhấn Enter để thêm nhãn...' },
});

const emit = defineEmits(['update:modelValue']);

const inputRef = ref(null);
const containerRef = ref(null);

function focusInput() {
    inputRef.value?.focus();
}

function handleKeydown(e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        const value = e.target.value.replace(',', '').trim();
        if (value && !props.modelValue.includes(value)) {
            const updated = [...props.modelValue, value];
            emit('update:modelValue', updated);
        }
        e.target.value = '';
    } else if (e.key === 'Backspace' && !e.target.value && props.modelValue.length) {
        const updated = [...props.modelValue];
        updated.pop();
        emit('update:modelValue', updated);
    }
}

function handleBlur(e) {
    const value = e.target.value.trim();
    if (value && !props.modelValue.includes(value)) {
        const updated = [...props.modelValue, value];
        emit('update:modelValue', updated);
    }
    e.target.value = '';
}

function removeTag(index) {
    const updated = [...props.modelValue];
    updated.splice(index, 1);
    emit('update:modelValue', updated);
}
</script>
