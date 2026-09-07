<template>
    <span class="ai-suggest-wrap" @click.stop @mousedown.stop @pointerdown.stop>
        <button
            type="button"
            class="ai-suggest-btn"
            :class="{ 'ai-suggest-btn-compact': compact, 'ai-suggest-btn-icon': iconOnly }"
            :disabled="aiLoading || disabled"
            :title="title"
            @click="fetchRecommendations"
        >
            <i v-if="aiLoading" class="fa fa-spinner fa-spin"></i>
            <i v-else class="fa fa-magic" style="color: #8b5cf6;"></i>
            <span v-if="!iconOnly">{{ compact ? 'AI' : label }}</span>
        </button>

        <Teleport to="body">
            <div v-if="showAiModal" class="ai-modal-overlay" @click.self="showAiModal = false">
                <div class="ai-modal-card">
                    <div class="ai-modal-header">
                        <div class="ai-modal-title">
                            <i class="fa fa-magic" style="color: #8b5cf6; margin-right: 6px;"></i>
                            <span>AI Đề Xuất Người Xử Lý Phù Hợp</span>
                        </div>
                        <button type="button" class="ai-modal-close" @click="showAiModal = false">&times;</button>
                    </div>
                    <div class="ai-modal-body">
                        <p class="ai-modal-subtitle">
                            Mô hình Deep Learning phân tích yêu cầu công việc, chuyên môn và tải công việc hiện tại.
                        </p>

                        <div v-if="aiRecommendations.length === 0" class="ai-empty-state">
                            <i class="fa fa-info-circle"></i> Chưa tìm thấy ứng viên phù hợp.
                        </div>

                        <div v-else class="ai-candidate-list">
                            <div
                                v-for="candidate in aiRecommendations"
                                :key="candidate.user_id"
                                class="ai-candidate-card"
                                :class="{ selected: String(selectedUserId) === String(candidate.user_id) }"
                            >
                                <div class="ai-candidate-header">
                                    <div class="ai-candidate-info">
                                        <div class="ai-candidate-avatar">{{ candidateInitial(candidate) }}</div>
                                        <div>
                                            <div class="ai-candidate-name">{{ candidateDisplayName(candidate) }}</div>
                                            <div class="ai-candidate-meta">
                                                <span>{{ candidate.job_title || 'Thành viên dự án' }}</span>
                                                <span class="ai-dot">•</span>
                                                <span>{{ candidate.active_tasks_count ?? 0 }} task đang làm</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ai-candidate-score-badge">
                                        {{ candidateConfidence(candidate) }}% Phù hợp
                                    </div>
                                </div>

                                <div v-if="candidate.match_reasons && candidate.match_reasons.length" class="ai-candidate-reasons">
                                    <div v-for="(reason, idx) in candidate.match_reasons" :key="idx" class="ai-reason-item">
                                        <i class="fa fa-check-circle" style="color: #10b981; margin-right: 4px;"></i>
                                        {{ reason }}
                                    </div>
                                </div>

                                <div class="ai-candidate-action">
                                    <button type="button" class="ai-select-btn" @click="applyCandidate(candidate)">
                                        <i class="fa fa-check" style="margin-right: 4px;"></i>
                                        Chọn người này
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </span>
</template>

<script setup>
import { computed, ref } from 'vue';
import {
    candidateConfidence,
    candidateDisplayName,
    candidateInitial,
    fetchAiRecommendations,
    saveTaskAssignee,
    sendAiFeedback,
} from '../../composables/useAiAssignee';

const props = defineProps({
    task: { type: Object, default: null },
    name: { type: String, default: '' },
    description: { type: String, default: '' },
    projectId: { type: [Number, String], default: null },
    issueType: { type: String, default: 'task' },
    priority: { type: String, default: 'medium' },
    taskId: { type: [Number, String], default: null },
    selectedUserId: { type: [Number, String], default: null },
    recommendUrl: { type: String, default: '/tasks/recommend-assignee' },
    feedbackUrl: { type: String, default: '/tasks/ai-feedback' },
    compact: { type: Boolean, default: false },
    iconOnly: { type: Boolean, default: false },
    autoSave: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    label: { type: String, default: '✨ AI Đề xuất' },
    title: { type: String, default: 'Dùng AI đề xuất người xử lý phù hợp' },
});

const emit = defineEmits(['select', 'applied']);

const aiLoading = ref(false);
const showAiModal = ref(false);
const aiRecommendations = ref([]);

const context = computed(() => ({
    name: props.task?.name ?? props.name,
    description: props.task?.description ?? props.description,
    project_id: props.task?.project_id ?? props.projectId,
    issue_type: props.task?.issue_type ?? props.issueType,
    priority: props.task?.priority ?? props.priority,
    task_id: props.task?.id ?? props.taskId,
}));

const fetchRecommendations = async () => {
    if (!context.value.name && !context.value.description) {
        alert('Hãy nhập tên hoặc mô tả công việc trước khi dùng AI đề xuất.');
        return;
    }

    aiLoading.value = true;
    try {
        aiRecommendations.value = await fetchAiRecommendations(context.value, props.recommendUrl);
        showAiModal.value = true;
    } catch (err) {
        console.error('AI Recommend Error:', err);
        alert(err.message || 'Lỗi kết nối khi gọi dịch vụ AI.');
    } finally {
        aiLoading.value = false;
    }
};

const applyCandidate = async (candidate) => {
    emit('select', candidate);

    if (props.autoSave && context.value.task_id) {
        try {
            const data = await saveTaskAssignee(context.value.task_id, candidate.user_id);
            if (!data.success) {
                alert('Không thể lưu người thực hiện.');
                return;
            }
            await sendAiFeedback(candidate.recommendation_id, 'accepted', props.feedbackUrl);
            emit('applied', { task: data.task, candidate });
        } catch (err) {
            console.error('AI assign save error:', err);
            alert('Không thể lưu người thực hiện.');
            return;
        }
    }

    showAiModal.value = false;
};
</script>
