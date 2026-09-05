<template>
    <div class="task-create-vue">
        <!-- Page Header Banner -->
        <HeaderBanner
            eyebrow="Công việc"
            eyebrowIcon="fa fa-tasks"
            title="Tạo công việc mới"
            description="Điền thông tin chi tiết để thêm công việc mới vào dự án của bạn."
        >
            <template #actions>
                <a :href="tasksIndexUrl" class="tp-btn tp-btn-secondary">
                    <i class="fa fa-arrow-left" style="color: #ffffff !important;"></i>
                    <span>Quay lại danh sách</span>
                </a>
            </template>
        </HeaderBanner>

        <form method="POST" :action="storeUrl" class="task-create-form">
            <input type="hidden" name="_token" :value="csrfToken">

            <!-- 2-Column Responsive Layout (70 / 30) -->
            <div class="edit-grid">
                <!-- Left Main Column (~70%) -->
                <div class="edit-col">
                    <!-- 1. Basic Info Card -->
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <div>
                                <h2 class="edit-card-title">
                                    <i class="fa fa-pencil-square-o"></i>
                                    Thông tin cơ bản
                                </h2>
                                <p class="edit-card-desc">Tên, mô tả và phân loại chính của công việc</p>
                            </div>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <label for="name">
                                    <span>Tên công việc <span class="required">*</span></span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    name="name"
                                    class="edit-input"
                                    :class="{ 'is-invalid': errors.name }"
                                    placeholder="Nhập tên gợi nhớ công việc (ví dụ: Thiết kế giao diện Dashboard)..."
                                    required
                                >
                                <div v-if="errors.name" class="edit-error">{{ errors.name[0] }}</div>
                                <div v-else class="edit-hint">Đặt tên rõ ràng, ngắn gọn và tập trung vào kết quả cần hoàn thành.</div>
                            </div>

                            <div class="edit-field">
                                <label for="description">Mô tả công việc</label>
                                <textarea
                                    id="description"
                                    name="description"
                                    v-model="form.description"
                                    class="edit-textarea"
                                    rows="8"
                                    style="min-height: 180px;"
                                    placeholder="Mô tả bối cảnh, tiêu chuẩn hoàn thành hoặc danh sách các công việc con..."
                                ></textarea>
                                <div v-if="errors.description" class="edit-error">{{ errors.description[0] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Issue Type Card (Compact Selector) -->
                    <div class="edit-card edit-card-compact">
                        <div class="edit-card-header">
                            <div>
                                <h2 class="edit-card-title">
                                    <i class="fa fa-tag"></i>
                                    Loại công việc
                                </h2>
                            </div>
                        </div>
                        <div class="edit-card-body">
                            <div class="issue-type-selector">
                                <label
                                    v-for="type in issueTypes"
                                    :key="type.value"
                                    class="issue-type-option"
                                >
                                    <input
                                        type="radio"
                                        name="issue_type"
                                        :value="type.value"
                                        v-model="form.issue_type"
                                    >
                                    <span :class="['issue-type-card', `issue-type-${type.value}`]">
                                        <i :class="type.icon"></i>
                                        <span>{{ type.label }}</span>
                                    </span>
                                </label>
                            </div>
                            <input type="hidden" name="issue_type" :value="form.issue_type">
                            <div v-if="errors.issue_type" class="edit-error">{{ errors.issue_type[0] }}</div>
                        </div>
                    </div>

                    <!-- 3. Parent Epic Card -->
                    <div class="edit-card edit-card-compact">
                        <div class="edit-card-header">
                            <div>
                                <h2 class="edit-card-title">
                                    <i class="fa fa-bolt"></i>
                                    Epic chứa công việc
                                </h2>
                                <p class="edit-card-desc">Gắn công việc này vào một Epic lớn hơn</p>
                            </div>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <label for="parent_id">Chọn Epic</label>
                                <div class="edit-select-wrapper">
                                    <select
                                        name="parent_id"
                                        id="parent_id"
                                        v-model="form.parent_id"
                                        class="edit-select"
                                    >
                                        <option value="">-- Không thuộc Epic nào --</option>
                                        <option
                                            v-for="epic in epics"
                                            :key="epic.id"
                                            :value="epic.id"
                                        >
                                            ⚡ {{ epic.issue_key || 'EPIC-' + epic.id }} - {{ epic.name }}
                                        </option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                </div>
                                <div v-if="errors.parent_id" class="edit-error">{{ errors.parent_id[0] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Status & Priority Card (Compact Selectors) -->
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <div>
                                <h2 class="edit-card-title">
                                    <i class="fa fa-sliders"></i>
                                    Trạng thái &amp; Độ ưu tiên
                                </h2>
                                <p class="edit-card-desc">Cấu hình tiến độ và mức độ quan trọng</p>
                            </div>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field" style="margin-bottom: 16px;">
                                <label>Trạng thái ban đầu</label>
                                <div class="status-selector">
                                    <label
                                        v-for="status in statuses"
                                        :key="status.value"
                                        class="status-option"
                                    >
                                        <input
                                            type="radio"
                                            name="status"
                                            :value="status.value"
                                            v-model="form.status"
                                        >
                                        <span :class="['status-card', `status-${status.value}`]">
                                            <i :class="status.icon"></i>
                                            <span>{{ status.label }}</span>
                                        </span>
                                    </label>
                                </div>
                                <input type="hidden" name="status" :value="form.status">
                            </div>

                            <div class="edit-field">
                                <label>Độ ưu tiên</label>
                                <div class="priority-selector">
                                    <label
                                        v-for="priority in priorities"
                                        :key="priority.value"
                                        class="priority-option"
                                    >
                                        <input
                                            type="radio"
                                            name="priority"
                                            :value="priority.value"
                                            v-model="form.priority"
                                        >
                                        <span :class="['priority-card', `priority-${priority.value}`]">
                                            <i :class="priority.icon"></i>
                                            <span>{{ priority.label }}</span>
                                        </span>
                                    </label>
                                </div>
                                <input type="hidden" name="priority" :value="form.priority">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Column (~30%) -->
                <div class="edit-col">
                    <!-- Sidebar Order 1: Dự án (Project) -->
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-folder"></i>
                                Dự án <span class="required">*</span>
                            </h2>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <div class="edit-select-wrapper">
                                    <select
                                        name="project_id"
                                        id="project_id"
                                        v-model="form.project_id"
                                        class="edit-select"
                                        :class="{ 'is-invalid': errors.project_id }"
                                        required
                                    >
                                        <option value="">-- Chọn dự án --</option>
                                        <option
                                            v-for="project in projects"
                                            :key="project.id"
                                            :value="project.id"
                                        >
                                            📁 {{ project.name }}
                                        </option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                </div>
                                <div v-if="errors.project_id" class="edit-error">{{ errors.project_id[0] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Order 2: Phân công (Assignee & Reporter) -->
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-users"></i>
                                Phân công công việc
                            </h2>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <div class="ai-label-wrapper" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <label for="assignee_id" style="margin-bottom: 0;">Người thực hiện</label>
                                    <button
                                        type="button"
                                        class="ai-suggest-btn"
                                        @click="fetchAiRecommendations"
                                        :disabled="aiLoading"
                                        title="Dùng mô hình Học Sâu PyTorch đề xuất người xử lý phù hợp"
                                    >
                                        <i v-if="aiLoading" class="fa fa-spinner fa-spin"></i>
                                        <i v-else class="fa fa-magic" style="color: #8b5cf6;"></i>
                                        <span>✨ AI Đề xuất</span>
                                    </button>
                                </div>
                                <div class="edit-select-wrapper">
                                    <select
                                        name="assignee_id"
                                        id="assignee_id"
                                        v-model="form.assignee_id"
                                        class="edit-select"
                                    >
                                        <option value="">Chưa phân công</option>
                                        <option
                                            v-for="user in users"
                                            :key="user.id"
                                            :value="user.id"
                                        >
                                            👤 {{ user.full_name || user.name }}
                                        </option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                </div>
                            </div>

                            <div class="edit-field">
                                <label for="reporter_id">Người báo cáo / Tạo</label>
                                <div class="edit-select-wrapper">
                                    <select
                                        name="reporter_id"
                                        id="reporter_id"
                                        v-model="form.reporter_id"
                                        class="edit-select"
                                    >
                                        <option value="">Chưa chọn</option>
                                        <option
                                            v-for="reporter in reporters"
                                            :key="reporter.id"
                                            :value="reporter.id"
                                        >
                                            👤 {{ reporter.full_name || reporter.name }}
                                        </option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Order 3: Sprint -->
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-calendar-check-o"></i>
                                Sprint
                            </h2>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <label for="sprint_id">Chọn Sprint</label>
                                <div class="edit-select-wrapper">
                                    <select
                                        name="sprint_id"
                                        id="sprint_id"
                                        v-model="form.sprint_id"
                                        class="edit-select"
                                    >
                                        <option value="">Backlog (Chưa vào Sprint)</option>
                                        <option
                                            v-for="sprint in sprints"
                                            :key="sprint.id"
                                            :value="sprint.id"
                                        >
                                            🏃 {{ sprint.name }} ({{ sprint.status }})
                                        </option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Order 4: Kế hoạch & Điểm số (Dates & Story Points) -->
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-clock-o"></i>
                                Kế hoạch &amp; Điểm số
                            </h2>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <label for="start_date">Ngày bắt đầu</label>
                                <input
                                    type="date"
                                    name="start_date"
                                    id="start_date"
                                    v-model="form.start_date"
                                    class="edit-input"
                                >
                            </div>
                            <div class="edit-field">
                                <label for="end_date">Ngày hết hạn (Due date)</label>
                                <input
                                    type="date"
                                    name="end_date"
                                    id="end_date"
                                    v-model="form.end_date"
                                    class="edit-input"
                                >
                            </div>
                            <div class="edit-field">
                                <label for="story_points">Story Points</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.5"
                                    name="story_points"
                                    id="story_points"
                                    v-model="form.story_points"
                                    class="edit-input"
                                    placeholder="Ví dụ: 3, 5, 8..."
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Order 5: Labels (Select & Quick Create) -->
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-tags"></i>
                                Nhãn (Labels)
                            </h2>
                            <button
                                type="button"
                                class="btn-quick-add-icon"
                                @click="labelSelectRef?.openCreateModal()"
                                title="Tạo nhãn mới nhanh"
                            >
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <LabelSelect
                                    ref="labelSelectRef"
                                    v-model="tags"
                                    :available-labels="availableLabels"
                                />
                                <input type="hidden" name="labels" :value="tags.join(', ')">
                                <div class="edit-hint">Bấm ô để chọn từ danh sách hoặc nhấn dấu (+) để tạo mới.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Actions -->
            <div class="tp-sticky-actions">
                <a :href="tasksIndexUrl" class="tp-btn tp-btn-light">
                    <i class="fa fa-times"></i>
                    <span>Hủy bỏ</span>
                </a>
                <button type="submit" class="tp-btn tp-btn-primary">
                    <i class="fa fa-plus-circle" style="color: #ffffff !important;"></i>
                    <span>Tạo công việc</span>
                </button>
            </div>
        </form>

        <!-- AI Assignee Recommendation Modal -->
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
                        Mô hình Deep Learning PyTorch phân tích yêu cầu công việc, chuyên môn & tải công việc hiện tại:
                    </p>
                    
                    <div v-if="aiRecommendations.length === 0" class="ai-empty-state">
                        <i class="fa fa-info-circle"></i> Chưa tìm thấy ứng viên phù hợp.
                    </div>

                    <div v-else class="ai-candidate-list">
                        <div
                            v-for="candidate in aiRecommendations"
                            :key="candidate.user_id"
                            class="ai-candidate-card"
                            :class="{ 'selected': form.assignee_id == candidate.user_id }"
                        >
                            <div class="ai-candidate-header">
                                <div class="ai-candidate-info">
                                    <div class="ai-candidate-avatar">
                                        {{ candidateInitial(candidate) }}
                                    </div>
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
                                    <i class="fa fa-check-circle" style="color: #10b981; margin-right: 4px;"></i> {{ reason }}
                                </div>
                            </div>

                            <div class="ai-candidate-action">
                                <button
                                    type="button"
                                    class="ai-select-btn"
                                    @click="applyAiAssignee(candidate.user_id)"
                                >
                                    <i class="fa fa-check" style="margin-right: 4px;"></i> Chọn người này
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue';
import HeaderBanner from '../common/HeaderBanner.vue';
import LabelSelect from '../common/LabelSelect.vue';

const labelSelectRef = ref(null);

const props = defineProps({
    projects: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    reporters: { type: Array, default: () => [] },
    epics: { type: Array, default: () => [] },
    sprints: { type: Array, default: () => [] },
    availableLabels: { type: Array, default: () => [] },
    csrfToken: { type: String, default: '' },
    storeUrl: { type: String, default: '/tasks' },
    recommendUrl: { type: String, default: '/tasks/recommend-assignee' },
    tasksIndexUrl: { type: String, default: '/tasks' },
    oldLabels: { type: String, default: '' },
    old: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    projectId: { type: [Number, String], default: null },
});

const issueTypes = [
    { value: 'task', label: 'Task', icon: 'fa fa-check-square-o' },
    { value: 'bug', label: 'Bug', icon: 'fa fa-bug' },
    { value: 'story', label: 'Story', icon: 'fa fa-bookmark' },
    { value: 'epic', label: 'Epic', icon: 'fa fa-bolt' },
    { value: 'subtask', label: 'Sub-task', icon: 'fa fa-code-fork' },
];

const statuses = [
    { value: 'todo', label: 'Cần làm', icon: 'fa fa-circle-o' },
    { value: 'in_progress', label: 'Đang làm', icon: 'fa fa-spinner' },
    { value: 'done', label: 'Hoàn thành', icon: 'fa fa-check-circle' },
];

const priorities = [
    { value: 'low', label: 'Thấp', icon: 'fa fa-arrow-down' },
    { value: 'medium', label: 'Trung bình', icon: 'fa fa-minus' },
    { value: 'high', label: 'Cao / Gấp', icon: 'fa fa-arrow-up' },
];

const form = reactive({
    project_id: props.projectId || props.old.project_id || '',
    name: props.old.name || '',
    description: props.old.description || '',
    issue_type: props.old.issue_type || 'task',
    parent_id: props.old.parent_id || '',
    status: props.old.status || 'todo',
    priority: props.old.priority || 'medium',
    assignee_id: props.old.assignee_id || '',
    reporter_id: props.old.reporter_id || '',
    sprint_id: props.old.sprint_id || '',
    start_date: props.old.start_date || '',
    end_date: props.old.end_date || '',
    story_points: props.old.story_points || '',
});

const tags = ref([]);
const aiLoading = ref(false);
const showAiModal = ref(false);
const aiRecommendations = ref([]);

const fetchAiRecommendations = async () => {
    aiLoading.value = true;
    try {
        const recommendUrl = props.recommendUrl || window.taskCreateData?.recommend_url || '/tasks/recommend-assignee';
        const csrf = props.csrfToken || window.taskCreateData?.csrf_token || '';

        const response = await fetch(recommendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                name: form.name,
                summary: form.name,
                description: form.description,
                project_id: form.project_id,
                issue_type: form.issue_type,
                priority: form.priority,
            }),
        });

        const data = await response.json();
        if (data.success && Array.isArray(data.recommendations)) {
            aiRecommendations.value = data.recommendations;
            showAiModal.value = true;
        } else {
            alert('Không thể tải gợi ý AI vào lúc này.');
        }
    } catch (err) {
        console.error('AI Recommend Error:', err);
        alert('Lỗi kết nối khi gọi dịch vụ AI.');
    } finally {
        aiLoading.value = false;
    }
};

const candidateDisplayName = (candidate) =>
    candidate.name || candidate.display_name || candidate.assignee || 'Thành viên';

const candidateInitial = (candidate) =>
    candidateDisplayName(candidate).charAt(0).toUpperCase();

const candidateConfidence = (candidate) => {
    if (candidate.confidence_percent != null && candidate.confidence_percent !== '') {
        return candidate.confidence_percent;
    }
    const score = Number(candidate.score);
    return Number.isFinite(score) ? Math.round(score * 100) : 0;
};

const applyAiAssignee = (userId) => {
    form.assignee_id = userId;
    showAiModal.value = false;
};

onMounted(() => {
    if (props.oldLabels) {
        tags.value = props.oldLabels.split(',').map(t => t.trim()).filter(t => t);
    }
    nextTick(() => {
        const projectId = props.projectId;
        if (projectId) {
            form.project_id = projectId;
        }
    });
});
</script>

<style scoped>
.ai-suggest-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
    color: #6b21a8;
    border: 1px solid #d8b4fe;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.ai-suggest-btn:hover {
    background: linear-gradient(135deg, #e9d5ff 0%, #d8b4fe 100%);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(168, 85, 247, 0.25);
}
.ai-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.ai-modal-card {
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 560px;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    overflow: hidden;
}
.ai-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: #faf5ff;
    border-bottom: 1px solid #f3e8ff;
}
.ai-modal-title {
    font-size: 1.05rem;
    font-weight: 700;
}
.ai-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
}
.ai-modal-body {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
}
.ai-modal-subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 16px;
}
.ai-candidate-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.ai-candidate-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px;
    transition: all 0.2s ease;
    background: #f8fafc;
}
.ai-candidate-card:hover {
    border-color: #a855f7;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(168, 85, 247, 0.12);
}
.ai-candidate-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ai-candidate-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.ai-candidate-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
.ai-candidate-name {
    font-weight: 700;
    color: #0f172a;
    font-size: 0.95rem;
}
.ai-candidate-meta {
    font-size: 0.8rem;
    color: #64748b;
}
.ai-dot {
    margin: 0 4px;
}
.ai-candidate-score-badge {
    background: #f3e8ff;
    color: #7e22ce;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 4px 10px;
    border-radius: 20px;
}
.ai-candidate-reasons {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed #cbd5e1;
    font-size: 0.82rem;
    color: #475569;
}
.ai-reason-item {
    margin-bottom: 2px;
}
.ai-candidate-action {
    margin-top: 12px;
    display: flex;
    justify-content: flex-end;
}
.ai-select-btn {
    background: #7c3aed;
    color: #ffffff;
    border: none;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}
.ai-select-btn:hover {
    background: #6d28d9;
}
</style>

