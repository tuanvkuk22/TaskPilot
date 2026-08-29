<template>
    <div class="label-select-container">
        <!-- Main Select Field Trigger (Click opens Modal Select) -->
        <div 
            class="edit-select-wrapper label-select-trigger"
            @click="openSelectModal"
            title="Bấm vào đây để mở modal chọn nhãn"
        >
            <div class="selected-labels-display">
                <template v-if="modelValue.length > 0">
                    <span 
                        v-for="tag in modelValue" 
                        :key="tag" 
                        class="tag-item label-pill-item"
                        @click.stop
                    >
                        #{{ tag }}
                        <button 
                            type="button" 
                            class="tag-remove" 
                            @click.stop="removeLabel(tag)"
                            title="Xóa nhãn"
                        >
                            <i class="fa fa-times"></i>
                        </button>
                    </span>
                </template>
                <span v-else class="select-placeholder">
                    <i class="fa fa-tags text-muted" style="margin-right: 4px;"></i>
                    -- Bấm vào đây để chọn nhãn --
                </span>
            </div>
            <div class="trigger-actions">
                <button 
                    v-if="modelValue.length > 0"
                    type="button" 
                    class="clear-all-btn" 
                    @click.stop="clearAll"
                    title="Bỏ chọn tất cả"
                >
                    <i class="fa fa-times-circle"></i>
                </button>
                <i class="fa fa-window-restore edit-select-icon text-muted"></i>
            </div>
        </div>

        <!-- Teleport Modals directly to body to ensure full screen modal display -->
        <Teleport to="body">
            <!-- MODAL 1: Modal Chọn Nhãn (Selection Modal) -->
            <transition name="tp-lbl-modal-fade">
                <div v-if="showSelectModal" class="tp-lbl-modal-overlay" @click.self="closeSelectModal">
                    <div class="tp-lbl-modal-card tp-lbl-select-modal">
                        <div class="tp-lbl-modal-header">
                            <div class="tp-lbl-modal-title-group">
                                <i class="fa fa-tags text-primary" style="font-size: 18px;"></i>
                                <h3 class="tp-lbl-modal-title">Chọn nhãn công việc</h3>
                                <span v-if="modelValue.length" class="tp-lbl-badge-count">{{ modelValue.length }} đã chọn</span>
                            </div>
                            <button type="button" class="tp-lbl-modal-close" @click="closeSelectModal">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>

                        <div class="tp-lbl-modal-body">
                            <!-- Search Box inside Modal -->
                            <div class="tp-lbl-search-box">
                                <i class="fa fa-search search-icon"></i>
                                <input
                                    ref="modalSearchInputRef"
                                    type="text"
                                    v-model="searchQuery"
                                    class="edit-input search-input"
                                    placeholder="Tìm kiếm nhãn hoặc gõ để tạo nhanh..."
                                    @keydown.enter.prevent="handleSearchEnter"
                                >
                                <button 
                                    v-if="searchQuery" 
                                    type="button" 
                                    class="clear-search-btn"
                                    @click="searchQuery = ''"
                                >
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>

                            <!-- Quick Add Banner if query is new -->
                            <div 
                                v-if="canQuickCreateFromSearch" 
                                class="tp-lbl-quick-banner"
                                @click="quickCreateFromSearch"
                            >
                                <i class="fa fa-plus-circle text-primary" style="margin-right: 6px;"></i>
                                <span>Tạo nhanh nhãn <strong>"#{{ searchQuery.trim() }}"</strong></span>
                                <span class="tp-lbl-badge-new">Tạo mới</span>
                            </div>

                            <!-- Labels List Grid -->
                            <div class="tp-lbl-grid">
                                <div v-if="filteredLabels.length === 0 && !canQuickCreateFromSearch" class="tp-lbl-empty">
                                    <i class="fa fa-info-circle" style="margin-right: 4px;"></i> Không tìm thấy nhãn phù hợp
                                </div>

                                <div
                                    v-for="label in filteredLabels"
                                    :key="label"
                                    class="tp-lbl-chip"
                                    :class="{ 'is-selected': isSelected(label) }"
                                    @click="toggleLabel(label)"
                                >
                                    <i :class="isSelected(label) ? 'fa fa-check-square check-icon' : 'fa fa-square-o uncheck-icon'"></i>
                                    <span class="chip-text">#{{ label }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="tp-lbl-modal-footer">
                            <button 
                                type="button" 
                                class="tp-btn tp-btn-light"
                                style="margin-right: auto;"
                                @click="openCreateModalFromSelection"
                            >
                                <i class="fa fa-plus-circle text-primary" style="margin-right: 4px;"></i>
                                <span>Thêm nhãn mới</span>
                            </button>
                            <button type="button" class="tp-btn tp-btn-primary" @click="closeSelectModal">
                                <i class="fa fa-check" style="color:#ffffff !important; margin-right: 4px;"></i>
                                <span>Hoàn tất</span>
                            </button>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- MODAL 2: Modal Nhập Nhãn Mới (Quick Create Input Modal) -->
            <transition name="tp-lbl-modal-fade">
                <div v-if="showCreateModal" class="tp-lbl-modal-overlay" @click.self="closeCreateModal">
                    <div class="tp-lbl-modal-card tp-lbl-create-modal">
                        <div class="tp-lbl-modal-header">
                            <div class="tp-lbl-modal-title-group">
                                <i class="fa fa-plus-circle text-primary" style="font-size: 18px;"></i>
                                <h3 class="tp-lbl-modal-title">Tạo nhanh nhãn mới</h3>
                            </div>
                            <button type="button" class="tp-lbl-modal-close" @click="closeCreateModal">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>

                        <div class="tp-lbl-modal-body">
                            <div class="edit-field">
                                <label for="modal-label-input">
                                    <span>Tên nhãn mới <span class="required">*</span></span>
                                </label>
                                <input
                                    id="modal-label-input"
                                    ref="createInputRef"
                                    type="text"
                                    v-model="newLabelName"
                                    class="edit-input"
                                    placeholder="Nhập tên nhãn (ví dụ: Frontend, Urgent, UI-Fix...)"
                                    @keydown.enter.prevent="submitCreateLabel"
                                >
                                <div v-if="createError" class="edit-error" style="margin-top: 4px;">{{ createError }}</div>
                                <div v-else class="edit-hint" style="margin-top: 4px;">Nhãn mới sẽ được tự động thêm vào danh sách và chọn cho công việc này.</div>
                            </div>
                        </div>

                        <div class="tp-lbl-modal-footer">
                            <button type="button" class="tp-btn tp-btn-light" @click="closeCreateModal">
                                Hủy bỏ
                            </button>
                            <button 
                                type="button" 
                                class="tp-btn tp-btn-primary"
                                :disabled="!newLabelName.trim()"
                                @click="submitCreateLabel"
                            >
                                <i class="fa fa-plus-circle" style="color:#ffffff !important; margin-right: 4px;"></i>
                                <span>Tạo &amp; Chọn nhãn</span>
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    availableLabels: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'createLabel']);

// Modal visibility states
const showSelectModal = ref(false);
const showCreateModal = ref(false);

const searchQuery = ref('');
const modalSearchInputRef = ref(null);
const createInputRef = ref(null);

const newLabelName = ref('');
const createError = ref('');

// Local pool of available labels
const labelPool = ref([...props.availableLabels]);

onMounted(() => {
    props.availableLabels.forEach(l => {
        if (l && !labelPool.value.includes(l)) {
            labelPool.value.push(l);
        }
    });
});

const filteredLabels = computed(() => {
    if (!searchQuery.value.trim()) {
        return labelPool.value;
    }
    const q = searchQuery.value.toLowerCase().trim();
    return labelPool.value.filter(label => label.toLowerCase().includes(q));
});

const canQuickCreateFromSearch = computed(() => {
    const q = searchQuery.value.trim();
    if (!q) return false;
    return !labelPool.value.some(l => l.toLowerCase() === q.toLowerCase());
});

function isSelected(label) {
    return props.modelValue.includes(label);
}

function toggleLabel(label) {
    let updated = [...props.modelValue];
    if (isSelected(label)) {
        updated = updated.filter(l => l !== label);
    } else {
        updated.push(label);
    }
    emit('update:modelValue', updated);
}

function removeLabel(label) {
    const updated = props.modelValue.filter(l => l !== label);
    emit('update:modelValue', updated);
}

function clearAll() {
    emit('update:modelValue', []);
}

function openSelectModal() {
    showSelectModal.value = true;
    searchQuery.value = '';
    nextTick(() => {
        modalSearchInputRef.value?.focus();
    });
}

function closeSelectModal() {
    showSelectModal.value = false;
    searchQuery.value = '';
}

function openCreateModal() {
    showCreateModal.value = true;
    newLabelName.value = '';
    createError.value = '';
    nextTick(() => {
        createInputRef.value?.focus();
    });
}

function openCreateModalFromSelection() {
    newLabelName.value = searchQuery.value.trim() || '';
    closeSelectModal();
    openCreateModal();
}

function closeCreateModal() {
    showCreateModal.value = false;
    newLabelName.value = '';
    createError.value = '';
}

function handleSearchEnter() {
    if (canQuickCreateFromSearch.value) {
        quickCreateFromSearch();
    } else if (filteredLabels.value.length > 0) {
        toggleLabel(filteredLabels.value[0]);
    }
}

function quickCreateFromSearch() {
    const name = searchQuery.value.trim();
    if (!name) return;
    addAndSelectLabel(name);
    searchQuery.value = '';
}

function submitCreateLabel() {
    const name = newLabelName.value.trim();
    if (!name) {
        createError.value = 'Vui lòng nhập tên nhãn';
        return;
    }
    addAndSelectLabel(name);
    closeCreateModal();
}

function addAndSelectLabel(name) {
    const existing = labelPool.value.find(l => l.toLowerCase() === name.toLowerCase());
    const finalName = existing || name;

    if (!existing) {
        labelPool.value.push(finalName);
        emit('createLabel', finalName);
    }

    if (!props.modelValue.includes(finalName)) {
        emit('update:modelValue', [...props.modelValue, finalName]);
    }
}

defineExpose({
    openSelectModal,
    openCreateModal,
});
</script>

<style scoped>
.label-select-container {
    position: relative;
    width: 100%;
}

.label-select-trigger {
    min-height: 42px;
    padding: 6px 12px !important;
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    transition: all 0.2s ease;
}

.label-select-trigger:hover {
    border-color: #2563eb !important;
    background: #f8fafc !important;
}

.selected-labels-display {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    flex: 1;
}

.label-pill-item {
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 9px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 16px;
    color: #1d4ed8;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.select-placeholder {
    color: #94a3b8;
    font-size: 13px;
}

.trigger-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.clear-all-btn {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 14px;
    padding: 0;
    line-height: 1;
    transition: color 0.15s ease;
}

.clear-all-btn:hover {
    color: #ef4444;
}

/* Modal Overlay & Card (High Z-Index, Isolated from Bootstrap 3) */
.tp-lbl-modal-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 23, 42, 0.6) !important;
    backdrop-filter: blur(6px) !important;
    -webkit-backdrop-filter: blur(6px) !important;
    z-index: 999999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 20px !important;
    box-sizing: border-box !important;
}

.tp-lbl-modal-card {
    background: #ffffff !important;
    border-radius: 16px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important;
    width: 100% !important;
    max-width: 500px !important;
    overflow: hidden !important;
    border: 1px solid #cbd5e1 !important;
    display: block !important;
    position: relative !important;
    animation: tp-lbl-pop 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.tp-lbl-select-modal {
    max-width: 500px !important;
}

.tp-lbl-create-modal {
    max-width: 440px !important;
}

@keyframes tp-lbl-pop {
    from {
        opacity: 0;
        transform: translateY(14px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.tp-lbl-modal-header {
    padding: 16px 20px !important;
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

.tp-lbl-modal-title-group {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.tp-lbl-modal-title {
    margin: 0 !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #0f172a !important;
}

.tp-lbl-badge-count {
    font-size: 11px !important;
    font-weight: 700 !important;
    background: #eff6ff !important;
    color: #2563eb !important;
    padding: 2px 8px !important;
    border-radius: 12px !important;
    border: 1px solid #bfdbfe !important;
}

.tp-lbl-modal-close {
    background: none !important;
    border: none !important;
    color: #94a3b8 !important;
    font-size: 16px !important;
    cursor: pointer !important;
    padding: 4px 8px !important;
    border-radius: 6px !important;
    transition: all 0.15s ease !important;
}

.tp-lbl-modal-close:hover {
    color: #ef4444 !important;
    background: #fef2f2 !important;
}

.tp-lbl-modal-body {
    padding: 20px !important;
    max-height: 380px !important;
    overflow-y: auto !important;
}

/* Modal Search Box */
.tp-lbl-search-box {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    margin-bottom: 14px !important;
}

.tp-lbl-search-box .search-icon {
    position: absolute !important;
    left: 12px !important;
    color: #94a3b8 !important;
    font-size: 13px !important;
}

.tp-lbl-search-box .search-input {
    padding-left: 34px !important;
    padding-right: 30px !important;
    width: 100% !important;
}

.clear-search-btn {
    position: absolute !important;
    right: 10px !important;
    background: none !important;
    border: none !important;
    color: #94a3b8 !important;
    cursor: pointer !important;
    font-size: 13px !important;
}

/* Quick Add Banner */
.tp-lbl-quick-banner {
    padding: 10px 14px !important;
    background: #eff6ff !important;
    border: 1px solid #bfdbfe !important;
    border-radius: 8px !important;
    color: #1e40af !important;
    font-size: 13px !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    margin-bottom: 14px !important;
    transition: background-color 0.15s ease !important;
}

.tp-lbl-quick-banner:hover {
    background: #dbeafe !important;
}

.tp-lbl-badge-new {
    margin-left: auto !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    background: #2563eb !important;
    color: #ffffff !important;
    padding: 2px 6px !important;
    border-radius: 4px !important;
}

/* Labels Grid inside Modal */
.tp-lbl-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)) !important;
    gap: 10px !important;
}

.tp-lbl-empty {
    grid-column: 1 / -1 !important;
    padding: 20px !important;
    text-align: center !important;
    color: #64748b !important;
    font-size: 13px !important;
}

.tp-lbl-chip {
    padding: 8px 12px !important;
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 8px !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #334155 !important;
    transition: all 0.15s ease !important;
    user-select: none !important;
}

.tp-lbl-chip:hover {
    background: #ffffff !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04) !important;
}

.tp-lbl-chip.is-selected {
    background: #eff6ff !important;
    border-color: #2563eb !important;
    color: #1d4ed8 !important;
    font-weight: 600 !important;
}

.check-icon {
    color: #2563eb !important;
    font-size: 14px !important;
}

.uncheck-icon {
    color: #94a3b8 !important;
    font-size: 14px !important;
}

.chip-text {
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

.tp-lbl-modal-footer {
    padding: 14px 20px !important;
    background: #f8fafc !important;
    border-top: 1px solid #e2e8f0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 10px !important;
}

.tp-lbl-modal-fade-enter-active,
.tp-lbl-modal-fade-leave-active {
    transition: opacity 0.2s ease !important;
}

.tp-lbl-modal-fade-enter-from,
.tp-lbl-modal-fade-leave-to {
    opacity: 0 !important;
}
</style>
