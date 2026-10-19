<template>
    <div class="task-create-vue">
        <!-- Page Header Banner -->
        <HeaderBanner
            v-if="isEditing"
            eyebrow="Công việc"
            eyebrowIcon="fa fa-pencil-square-o"
            title="Chỉnh sửa công việc"
            description="Cập nhật thông tin, tiến độ và người phụ trách công việc."
        >
            <template #actions>
                <a :href="tasksIndexUrl" class="tp-btn tp-btn-secondary">
                    <i class="fa fa-arrow-left" style="color: #ffffff !important;"></i>
                    <span>Quay lại danh sách</span>
                </a>
            </template>
        </HeaderBanner>

        <HeaderBanner v-else
            eyebrow="Công việc"
            eyebrowIcon="fa fa-tasks"
            title="Tạo công việc mới"
            description="Điền thông tin chi tiết để thêm công việc mới vào dự án của bạn."
            :title="isEditing ? 'Chỉnh sửa công việc' : 'Tạo công việc mới'"
            :description="isEditing ? 'Cập nhật thông tin, tiến độ và người phụ trách công việc.' : 'Điền thông tin chi tiết để thêm công việc mới vào dự án của bạn.'"
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
            <input v-if="isEditing" type="hidden" name="_method" value="PUT">

            <!-- Validation Error Summary Alert -->
            <div v-if="hasErrors" class="alert alert-danger mb-4" style="border-radius: 10px; border-left: 4px solid #ef4444; background: #fef2f2; color: #991b1b; padding: 14px 18px;">
                <div class="d-flex align-items-center gap-2 mb-1 font-weight-bold" style="font-size: 14px;">
                    <i class="fa fa-exclamation-circle text-danger"></i>
                    <span>Vui lòng kiểm tra lại các thông tin chưa hợp lệ:</span>
                </div>
                <ul class="m-0 ps-4" style="font-size: 13px;">
                    <li v-for="(errList, field) in errors" :key="field">
                        {{ Array.isArray(errList) ? errList[0] : errList }}
                    </li>
                </ul>
            </div>

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
                                <div class="status-selector" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <label
                                        v-for="status in statuses"
                                        :key="status.value"
                                        class="status-option"
                                        style="cursor: pointer;"
                                    >
                                        <input
                                            type="radio"
                                            name="status"
                                            :value="status.value"
                                            v-model="form.status"
                                            style="display: none;"
                                        >
                                        <span
                                            :class="['status-card', `status-${status.value}`]"
                                            :style="{
                                                borderColor: form.status === status.value ? (status.color || '#3b82f6') : '#cbd5e1',
                                                backgroundColor: form.status === status.value ? (status.color ? status.color + '15' : '#eff6ff') : '#ffffff',
                                                boxShadow: form.status === status.value ? `0 0 0 2px ${status.color ? status.color + '33' : '#3b82f633'}` : 'none',
                                                color: form.status === status.value ? (status.color || '#0f172a') : '#475569',
                                                display: 'inline-flex',
                                                alignItems: 'center',
                                                gap: '6px',
                                                padding: '6px 14px',
                                                borderRadius: '8px',
                                                borderWidth: '1.5px',
                                                borderStyle: 'solid',
                                                fontSize: '13px',
                                                fontWeight: '600',
                                                transition: 'all 0.15s ease'
                                            }"
                                        >
                                            <i :class="status.icon" :style="{ color: status.color || '#64748b' }"></i>
                                            <span>{{ status.label }}</span>
                                        </span>
                                    </label>
                                </div>
                                <input type="hidden" name="status" :value="form.status">
                                <div v-if="errors.status" class="edit-error mt-1">{{ errors.status[0] }}</div>
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
                                <div class="ai-label-wrapper">
                                    <label for="assignee_id" style="margin-bottom: 0;">Người thực hiện</label>
                                    <AiAssigneeSuggest
                                        :name="form.name"
                                        :description="form.description"
                                        :project-id="form.project_id"
                                        :issue-type="form.issue_type"
                                        :priority="form.priority"
                                        :selected-user-id="form.assignee_id"
                                        :recommend-url="recommendUrl"
                                        @select="applyAiAssignee"
                                    />
                                </div>
                                <div class="edit-select-wrapper">
                                    <select
                                        name="assignee_id"
                                        id="assignee_id"
                                        v-model="form.assignee_id"
                                        class="edit-select"
                                        :disabled="!form.project_id || isLoadingProjectData"
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
                                        :disabled="!form.project_id || isLoadingProjectData"
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
                    <i :class="isEditing ? 'fa fa-save' : 'fa fa-plus-circle'" style="color: #ffffff !important;"></i>
                    <span v-if="isEditing">Cập nhật công việc</span>
                    <template v-else>
                    <span>Tạo công việc</span>
                    </template>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick, computed, watch } from 'vue';
import HeaderBanner from '../common/HeaderBanner.vue';
import LabelSelect from '../common/LabelSelect.vue';
import AiAssigneeSuggest from '../common/AiAssigneeSuggest.vue';

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
    formDataUrl: { type: String, default: '/tasks/form-data' },
    recommendUrl: { type: String, default: '/tasks/recommend-assignee' },
    tasksIndexUrl: { type: String, default: '/tasks' },
    oldLabels: { type: String, default: '' },
    old: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    projectId: { type: [Number, String], default: null },
    defaultStatus: { type: String, default: null },
    activeStatuses: { type: Array, default: () => [] },
    effectiveWorkflow: { type: Object, default: () => null },
    task: { type: Object, default: null },
    isEditing: { type: Boolean, default: false },
});

const initialTask = props.task || {};
const hasErrors = computed(() => props.errors && Object.keys(props.errors).length > 0);

const issueTypes = [
    { value: 'task', label: 'Task', icon: 'fa fa-check-square-o' },
    { value: 'bug', label: 'Bug', icon: 'fa fa-bug' },
    { value: 'story', label: 'Story', icon: 'fa fa-bookmark' },
    { value: 'epic', label: 'Epic', icon: 'fa fa-bolt' },
    { value: 'subtask', label: 'Công việc con', icon: 'fa fa-code-fork' },
];

const defaultStatuses = [
    { value: 'todo', label: 'Sẽ làm', icon: 'fa fa-clock-o', color: '#64748b' },
    { value: 'in_progress', label: 'Đang làm', icon: 'fa fa-spinner', color: '#2563eb' },
    { value: 'done', label: 'Hoàn thành', icon: 'fa fa-check-circle', color: '#10b981' },
];

function formatWorkflowStatuses(list) {
    if (!list || !Array.isArray(list) || list.length === 0) return defaultStatuses;
    return list
        .filter(s => (s.is_active ?? true) !== false)
        .map(s => ({
            value: s.key || s.value,
            label: s.name || s.label,
            icon: s.icon ? (s.icon.startsWith('fa') ? s.icon : `fa ${s.icon}`) : 'fa fa-circle-o',
            color: s.color || '#64748b',
            is_initial: Boolean(s.is_initial)
        }));
}

const statuses = ref(
    props.activeStatuses && props.activeStatuses.length
        ? formatWorkflowStatuses(props.activeStatuses)
        : (props.effectiveWorkflow?.statuses ? formatWorkflowStatuses(props.effectiveWorkflow.statuses) : defaultStatuses)
);

const priorities = [
    { value: 'low', label: 'Thấp', icon: 'fa fa-arrow-down' },
    { value: 'medium', label: 'Trung bình', icon: 'fa fa-minus' },
    { value: 'high', label: 'Cao / Gấp', icon: 'fa fa-arrow-up' },
];

const form = reactive({
    project_id: props.old.project_id || props.projectId || initialTask.project_id || '',
    name: props.old.name || initialTask.name || '',
    description: props.old.description || initialTask.description || '',
    issue_type: props.old.issue_type || initialTask.issue_type || 'task',
    parent_id: props.old.parent_id || initialTask.parent_id || '',
    status: props.old.status || initialTask.status || props.defaultStatus || (statuses.value[0]?.value || 'todo'),
    priority: props.old.priority || initialTask.priority || 'medium',
    assignee_id: props.old.assignee_id || initialTask.assignee_id || '',
    reporter_id: props.old.reporter_id || initialTask.reporter_id || '',
    sprint_id: props.old.sprint_id || initialTask.sprint_id || '',
    start_date: props.old.start_date || initialTask.start_date || '',
    end_date: props.old.end_date || initialTask.end_date || '',
    story_points: props.old.story_points || initialTask.story_points || '',
});

const tags = ref([]);
const users = ref(props.users);
const reporters = ref(props.reporters);
const epics = ref(props.epics);
const sprints = ref(props.sprints);
const isLoadingProjectData = ref(false);
const recommendUrl = computed(() => props.recommendUrl || window.taskCreateData?.recommend_url || '/tasks/recommend-assignee');

const applyAiAssignee = (candidate) => {
    form.assignee_id = candidate.user_id;
};

const containsId = (items, id) => items.some((item) => String(item.id) === String(id));

watch(() => form.project_id, async (projectId) => {
    if (!projectId) {
        users.value = [];
        reporters.value = [];
        epics.value = [];
        sprints.value = [];
        form.assignee_id = '';
        form.reporter_id = '';
        form.parent_id = '';
        form.sprint_id = '';
        return;
    }

    isLoadingProjectData.value = true;

    try {
        const params = new URLSearchParams({ project_id: String(projectId) });
        const response = await fetch(`${props.formDataUrl}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Unable to load project task options.');
        }

        const data = await response.json();
        users.value = data.users || [];
        reporters.value = data.reporters || [];
        epics.value = data.epics || [];
        sprints.value = data.sprints || [];

        if (data.statuses && data.statuses.length) {
            statuses.value = formatWorkflowStatuses(data.statuses);
            const validStatusKeys = statuses.value.map(s => s.value);
            if (!validStatusKeys.includes(form.status)) {
                const initialStatus = statuses.value.find(s => s.is_initial) || statuses.value[0];
                if (initialStatus) {
                    form.status = initialStatus.value;
                }
            }
        }

        if (form.assignee_id && !containsId(users.value, form.assignee_id)) form.assignee_id = '';
        if (form.reporter_id && !containsId(reporters.value, form.reporter_id)) form.reporter_id = '';
        if (form.parent_id && !containsId(epics.value, form.parent_id)) form.parent_id = '';
        if (form.sprint_id && !containsId(sprints.value, form.sprint_id)) form.sprint_id = '';
    } catch (error) {
        console.error(error);
        users.value = [];
        reporters.value = [];
        epics.value = [];
        sprints.value = [];
    } finally {
        isLoadingProjectData.value = false;
    }
}, { immediate: true });

onMounted(() => {
    const initialLabels = props.oldLabels || initialTask.labels || '';
    if (initialLabels) {
        tags.value = initialLabels.split(',').map(t => t.trim()).filter(t => t);
    }
    nextTick(() => {
        const projectId = props.projectId;
        if (projectId) {
            form.project_id = projectId;
        }
    });
});
</script>
