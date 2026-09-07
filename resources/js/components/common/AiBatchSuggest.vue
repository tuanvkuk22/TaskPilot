<template>
    <span class="ai-suggest-wrap">
        <button
            type="button"
            class="ai-suggest-btn"
            :disabled="aiLoading || applying || !taskIds.length"
            :title="buttonTitle"
            @click="runBatch"
        >
            <i v-if="aiLoading || applying" class="fa fa-spinner fa-spin"></i>
            <i v-else class="fa fa-magic" style="color: #8b5cf6;"></i>
            <span>{{ buttonLabel }}</span>
        </button>
        <button
            type="button"
            class="ai-suggest-btn ai-reset-btn"
            :disabled="aiLoading || applying || resetting"
            title="Gỡ phân công các việc đã đánh dấu test AI để gợi ý lại"
            @click="resetPool"
        >
            <i v-if="resetting" class="fa fa-spinner fa-spin"></i>
            <i v-else class="fa fa-undo"></i>
            <span>Reset test AI</span>
        </button>

        <Teleport to="body">
            <div v-if="showModal" class="ai-modal-overlay" @click.self="showModal = false">
                <div class="ai-modal-card ai-modal-wide">
                    <div class="ai-modal-header">
                        <div class="ai-modal-title">
                            <i class="fa fa-magic" style="color: #8b5cf6; margin-right: 6px;"></i>
                            <span>AI gợi ý tất cả công việc chưa phân công</span>
                        </div>
                        <button type="button" class="ai-modal-close" @click="showModal = false">&times;</button>
                    </div>
                    <div class="ai-modal-body">
                        <p class="ai-modal-subtitle">
                            AI đã chọn người phù hợp nhất cho từng việc. Bỏ chọn dòng không muốn gán, rồi bấm Áp dụng tất cả.
                        </p>

                        <div v-if="!rows.length" class="ai-empty-state">
                            <i class="fa fa-info-circle"></i> Không có gợi ý nào để áp dụng.
                        </div>

                        <div v-else class="ai-batch-list">
                            <div v-for="row in rows" :key="row.task_id" class="ai-batch-row">
                                <label class="ai-batch-check">
                                    <input type="checkbox" v-model="row.include" :disabled="!row.suggested">
                                </label>
                                <div class="ai-batch-task">
                                    <div class="ai-home-item-title">{{ row.task_name }}</div>
                                    <div class="ai-home-item-meta">{{ row.issue_type || 'task' }} · {{ row.priority || 'medium' }}</div>
                                </div>
                                <div v-if="row.recommendations.length" class="ai-batch-pick">
                                    <select v-model="row.selectedUserId" class="edit-select">
                                        <option
                                            v-for="candidate in row.recommendations"
                                            :key="candidate.user_id"
                                            :value="String(candidate.user_id)"
                                        >
                                            {{ candidateDisplayName(candidate) }} · {{ candidateConfidence(candidate) }}%
                                        </option>
                                    </select>
                                </div>
                                <div v-else class="ai-home-item-meta">Chưa có ứng viên</div>
                            </div>
                        </div>
                    </div>
                    <div class="ai-modal-footer">
                        <button type="button" class="tp-btn tp-btn-light" @click="showModal = false">Hủy</button>
                        <button
                            type="button"
                            class="ai-select-btn"
                            :disabled="applying || !selectedCount"
                            @click="applyAll"
                        >
                            <i v-if="applying" class="fa fa-spinner fa-spin"></i>
                            <i v-else class="fa fa-check"></i>
                            Áp dụng tất cả ({{ selectedCount }})
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </span>
</template>

<script setup>
import { computed, ref } from 'vue';
import {
    applyAiBatchAssignments,
    assigneeFromCandidate,
    candidateConfidence,
    candidateDisplayName,
    fetchAiBatchRecommendations,
    resetAiTestAssignments,
} from '../../composables/useAiAssignee';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    projectId: { type: [Number, String], default: null },
});

const emit = defineEmits(['applied', 'reset']);

const aiLoading = ref(false);
const applying = ref(false);
const resetting = ref(false);
const showModal = ref(false);
const rows = ref([]);

const unassignedTasks = computed(() => {
    return (props.tasks || []).filter((task) => {
        if (['done', 'completed', 'resolved', 'closed'].includes(task.status)) {
            return false;
        }
        return !task.assignee_id && !task.assignee;
    });
});

const taskIds = computed(() => unassignedTasks.value.map((task) => task.id));

const selectedCount = computed(() => rows.value.filter((row) => row.include && row.selectedUserId).length);

const buttonLabel = computed(() => {
    if (aiLoading.value) {
        return 'Đang gợi ý...';
    }
    if (applying.value) {
        return 'Đang gán...';
    }
    const count = taskIds.value.length;
    return count ? `AI gợi ý tất cả (${count})` : 'AI gợi ý tất cả';
});

const buttonTitle = computed(() => {
    if (!taskIds.value.length) {
        return 'Không còn công việc chưa phân công';
    }
    return 'Dùng AI đề xuất người xử lý cho toàn bộ công việc chưa phân công';
});

const runBatch = async () => {
    if (!taskIds.value.length) {
        alert('Không còn công việc chưa phân công để gợi ý.');
        return;
    }

    aiLoading.value = true;
    try {
        const items = await fetchAiBatchRecommendations(taskIds.value);
        rows.value = items.map((item) => ({
            ...item,
            include: Boolean(item.suggested),
            selectedUserId: item.suggested ? String(item.suggested.user_id) : '',
        }));
        showModal.value = true;
    } catch (err) {
        console.error('AI batch error:', err);
        alert(err.message || 'Không thể gợi ý hàng loạt lúc này.');
    } finally {
        aiLoading.value = false;
    }
};

const applyAll = async () => {
    const selected = rows.value.filter((row) => row.include && row.selectedUserId);
    if (!selected.length) {
        return;
    }

    applying.value = true;
    try {
        const assignments = selected.map((row) => {
            const candidate = (row.recommendations || []).find(
                (item) => String(item.user_id) === String(row.selectedUserId)
            ) || row.suggested;
            return {
                task_id: row.task_id,
                user_id: Number(row.selectedUserId),
                score: candidate?.score ?? 0,
                match_reasons: candidate?.match_reasons || [],
                candidate,
            };
        });

        await applyAiBatchAssignments(assignments.map(({ candidate, ...payload }) => payload));

        emit('applied', assignments.map((row) => ({
            taskId: row.task_id,
            candidate: row.candidate || assigneeFromCandidate({
                user_id: row.user_id,
                name: 'Thành viên',
            }),
        })));

        showModal.value = false;
    } catch (err) {
        console.error('AI batch assign error:', err);
        alert(err.message || 'Không thể lưu phân công hàng loạt.');
    } finally {
        applying.value = false;
    }
};

const resetPool = async () => {
    if (!confirm('Gỡ người xử lý trên các việc test AI để gợi ý lại từ đầu? Việc đã đóng/gán từ dataset sẽ giữ nguyên.')) {
        return;
    }

    resetting.value = true;
    try {
        const result = await resetAiTestAssignments({
            projectId: props.projectId || null,
        });
        emit('reset', result.tasks || []);
        alert(`Đã reset ${result.reset_count || 0} công việc test AI. Có thể bấm AI gợi ý tất cả lại.`);
    } catch (err) {
        console.error('AI test reset error:', err);
        alert(err.message || 'Không thể reset dữ liệu test AI.');
    } finally {
        resetting.value = false;
    }
};
</script>
