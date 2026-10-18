<template>
    <div class="task-index-vue">
        <div v-if="successMessage" class="tp-task-toast" role="status">
            <i class="fa fa-check-circle"></i>
            <span>{{ successMessage }}</span>
            <button type="button" aria-label="Đóng thông báo" @click="successMessage = ''">&times;</button>
        </div>

        <!-- Page Header Banner (Optional header if not in project context) -->
        <HeaderBanner
            eyebrow="TaskPilot Workspace"
            eyebrowIcon="fa fa-tasks"
            title="Quản lý Công việc"
            description="Theo dõi tiến độ, phân công nhiệm vụ và tối ưu hóa hiệu suất làm việc bằng bảng Kanban và danh sách."
        >
            <template #actions>
                <a :href="createTaskUrl" class="tp-btn tp-btn-primary">
                    <i class="fa fa-plus-circle" style="color: #ffffff !important;"></i>
                    <span>Tạo công việc mới</span>
                </a>
            </template>
        </HeaderBanner>

        <!-- KPI Metric Cards (Live Real-Time Reactive Stats) -->
        <div class="tp-stats-grid mb-4">
            <StatCard label="Tổng công việc" :value="liveStats.total" icon="fa fa-list-alt" variant="total" />
            <StatCard label="Đang thực hiện" :value="liveStats.in_progress" icon="fa fa-spinner" variant="in_progress" />
            <StatCard label="Hoàn thành" :value="liveStats.done" icon="fa fa-check-circle" variant="done" />
            <StatCard label="Ưu tiên cao / Gấp" :value="liveStats.high_priority" icon="fa fa-exclamation-triangle" variant="urgent" />
        </div>

        <!-- Professional Multi-Filter Bar -->
        <div class="tp-filter-bar d-flex flex-wrap gap-2 align-items-center mb-3">
            <div class="tp-filter-inputs d-flex flex-wrap gap-2 align-items-center flex-grow-1">
                <!-- Search -->
                <div class="tp-search-box flex-grow-1" style="min-width: 220px;">
                    <i class="fa fa-search"></i>
                    <input
                        type="text"
                        v-model="searchQuery"
                        class="edit-input"
                        placeholder="Tìm theo tên, key, nhãn, người làm..."
                    >
                </div>

                <!-- Project Filter (If multiple projects) -->
                <div v-if="projects.length > 1" class="edit-select-wrapper" style="min-width: 170px;">
                    <select v-model="selectedProjectFilter" class="edit-select">
                        <option value="">📁 Dự án: Tất cả</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">
                            📁 {{ project.name }}
                        </option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Assignee Filter -->
                <div class="edit-select-wrapper" style="min-width: 160px;">
                    <select v-model="selectedAssigneeFilter" class="edit-select">
                        <option value="">👤 Người làm: Tất cả</option>
                        <option value="unassigned">-- Chưa phân công --</option>
                        <option v-for="u in assigneesList" :key="u.id" :value="u.id">
                            👤 {{ u.name }}
                        </option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Reporter Filter -->
                <div class="edit-select-wrapper" style="min-width: 160px;">
                    <select v-model="selectedReporterFilter" class="edit-select">
                        <option value="">📣 Người báo cáo: Tất cả</option>
                        <option v-for="r in reportersList" :key="r.id" :value="r.id">
                            📣 {{ r.name }}
                        </option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Issue Type Filter -->
                <div class="edit-select-wrapper" style="min-width: 140px;">
                    <select v-model="selectedTypeFilter" class="edit-select">
                        <option value="">⚡ Loại Issue: Tất cả</option>
                        <option value="task">Task</option>
                        <option value="bug">Bug</option>
                        <option value="story">Story</option>
                        <option value="epic">Epic</option>
                        <option value="subtask">Sub-task</option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Priority Filter -->
                <div class="edit-select-wrapper" style="min-width: 140px;">
                    <select v-model="selectedPriorityFilter" class="edit-select">
                        <option value="">🔥 Độ ưu tiên: Tất cả</option>
                        <option value="high">Cao / Gấp</option>
                        <option value="medium">Trung bình</option>
                        <option value="low">Thấp</option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Status Filter -->
                <div class="edit-select-wrapper" style="min-width: 150px;">
                    <select v-model="selectedStatusFilter" class="edit-select">
                        <option value="">📌 Trạng thái: Tất cả</option>
                        <option v-for="col in columns" :key="col.key" :value="col.key">
                            {{ col.label }}
                        </option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Reset Filter Button -->
                <button v-if="hasActiveFilters" class="tp-btn tp-btn-light btn-sm ms-1" @click="resetFilters" title="Xóa tất cả bộ lọc">
                    <i class="fa fa-times text-danger"></i> Xóa lọc
                </button>
            </div>

            <!-- View Toggle Buttons (Kanban / Table) -->
            <div class="tp-view-toggle ms-auto">
                <button
                    class="tp-view-btn"
                    :class="{ active: viewMode === 'kanban' }"
                    @click="viewMode = 'kanban'"
                >
                    <i class="fa fa-columns"></i>
                    <span>Bảng Kanban</span>
                </button>
                <button
                    class="tp-view-btn"
                    :class="{ active: viewMode === 'table' }"
                    @click="viewMode = 'table'"
                >
                    <i class="fa fa-th-list"></i>
                    <span>Danh sách</span>
                </button>
            </div>
        </div>

        <!-- Dedicated AI Smart Assignment Card & Action Bar -->
        <div class="tp-ai-action-card mb-4">
            <div class="tp-ai-action-header">
                <div class="tp-ai-badge-icon">
                    <i class="fa fa-magic"></i>
                </div>
                <div class="tp-ai-action-text">
                    <h4 class="tp-ai-action-title">Trợ lý AI Phân công Thông minh</h4>
                    <p class="tp-ai-action-desc">
                        <template v-if="unassignedCount > 0">
                            Phát hiện <strong>{{ unassignedCount }}</strong> công việc chưa có người thực hiện. Bấm <strong>AI gợi ý tất cả</strong> để tự động đề xuất phân bổ tối ưu theo kỹ năng, hoặc lọc danh sách.
                        </template>
                        <template v-else>
                            Tất cả công việc đã có người phụ trách. Bạn có thể bấm <strong>Reset test AI</strong> nếu muốn thử nghiệm lại gợi ý.
                        </template>
                    </p>
                </div>
            </div>
            <div class="tp-ai-action-buttons">
                <button
                    v-if="unassignedCount > 0"
                    type="button"
                    class="tp-ai-filter-toggle-btn"
                    :class="{ 'is-active': selectedAssigneeFilter === 'unassigned' }"
                    @click="selectedAssigneeFilter = selectedAssigneeFilter === 'unassigned' ? '' : 'unassigned'"
                    title="Lọc nhanh danh sách chỉ hiển thị việc chưa phân công"
                >
                    <i class="fa fa-filter"></i>
                    <span>{{ selectedAssigneeFilter === 'unassigned' ? 'Bỏ lọc chưa phân công' : 'Lọc việc chưa giao' }}</span>
                </button>
                <AiBatchSuggest
                    :tasks="unassignedOpenTasks"
                    :project-id="selectedProjectFilter || null"
                    @applied="applyBatchResults"
                    @reset="applyAiTestReset"
                />
            </div>
        </div>

        <!-- 1. KANBAN BOARD VIEW WITH HTML5 DRAG & DROP -->
        <div v-if="viewMode === 'kanban'" class="tp-kanban-board">
            <!-- Columns Definition -->
            <div
                v-for="col in columns"
                :key="col.key"
                class="tp-kanban-column"
                :class="{ 'is-drop-target': activeDropColumn === col.key }"
                @dragover.prevent="handleDragOver($event, col.key)"
                @dragenter.prevent="handleDragEnter($event, col.key)"
                @dragleave="handleDragLeave($event, col.key)"
                @drop="handleDrop($event, col.key)"
            >
                <!-- Column Header -->
                <div class="tp-kanban-header">
                    <h3 class="tp-kanban-title">
                        <i :class="col.icon" :style="{ color: col.iconColor }"></i>
                        <span>{{ col.label }}</span>
                    </h3>
                    <div class="d-flex align-items-center gap-2">
                        <span class="tp-kanban-count" :style="{ background: col.badgeBg, color: col.badgeColor }">
                            {{ getTasksByStatus(col.key).length }}
                        </span>
                        <a :href="`${createTaskUrl}?status=${col.key}`" class="kanban-add-btn" title="Tạo nhanh công việc mới trong cột này">
                            <i class="fa fa-plus"></i>
                        </a>
                    </div>
                </div>

                <!-- Cards Drag & Drop Container -->
                <div class="tp-kanban-cards-container">
                    <div
                        v-for="task in getTasksByStatus(col.key)"
                        :key="task.id"
                        class="tp-kanban-card"
                        :class="{ 'is-dragging': draggedTaskId === task.id }"
                        draggable="true"
                        @dragstart="handleDragStart($event, task)"
                        @dragend="handleDragEnd"
                        @click="goToTask(task.id)"
                    >
                        <div class="tp-kanban-card-meta">
                            <div class="d-flex align-items-center gap-1">
                                <IssueTypeBadge :type="task.issue_type" />
                                <span class="task-key-tag">{{ task.project ? task.project.project_code : 'TASK' }}-{{ task.id }}</span>
                            </div>
                            <PriorityBadge :priority="task.priority" />
                        </div>

                        <h4 class="tp-kanban-card-title" :style="col.key === 'done' ? 'text-decoration: line-through; color: #94a3b8;' : ''">
                            {{ task.name }}
                        </h4>

                        <!-- Labels Pills -->
                        <div v-if="task.labels" class="kanban-card-labels">
                            <span v-for="tag in task.labels.split(',')" :key="tag" class="tag-item">
                                #{{ tag.trim() }}
                            </span>
                        </div>

                        <!-- Card Footer -->
                        <div class="tp-kanban-card-footer">
                            <span class="project-name-text">
                                <i class="fa fa-folder-o text-muted me-1"></i>
                                {{ task.project ? task.project.name : 'N/A' }}
                            </span>

                            <div class="d-flex align-items-center gap-2">
                                <span v-if="task.story_points" class="points-badge" style="font-size: 11px; padding: 1px 6px;">
                                    {{ task.story_points }}pts
                                </span>
                                <AiAssigneeSuggest
                                    v-if="!task.assignee_id && !task.assignee"
                                    :task="task"
                                    compact
                                    auto-save
                                    @applied="(payload) => patchTaskAssignee(task, payload.candidate)"
                                />
                                <UserAvatarPill :name="task.assignee ? (task.assignee.full_name || task.assignee.name) : ''" />
                            </div>
                        </div>
                    </div>

                    <!-- Column Empty Drop Indicator -->
                    <div v-if="getTasksByStatus(col.key).length === 0" class="tp-kanban-empty">
                        <i class="fa fa-hand-o-down text-muted font-size-18 mb-1"></i>
                        <span>Kéo thả công việc vào đây</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TABLE VIEW -->
        <div v-else>
            <div v-if="filteredTasks.length > 0" class="tp-table-card">
                <table class="tp-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Loại</th>
                            <th>Công việc</th>
                            <th>Dự án</th>
                            <th>Người thực hiện</th>
                            <th>Độ ưu tiên</th>
                            <th>Trạng thái</th>
                            <th>Hạn hoàn thành</th>
                            <th style="text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="task in filteredTasks" :key="task.id">
                            <td>
                                <IssueTypeBadge :type="task.issue_type" />
                            </td>
                            <td>
                                <a :href="`/tasks/${task.id}`" style="font-weight: 600; color: #0f172a; text-decoration: none;">
                                    {{ task.name }}
                                </a>
                                <div v-if="task.labels" style="margin-top: 4px; display: flex; gap: 4px;">
                                    <span v-for="tag in task.labels.split(',')" :key="tag" class="tag-item" style="font-size: 10px; padding: 1px 6px;">
                                        #{{ tag.trim() }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 500; color: #475569;">
                                    <i class="fa fa-folder-o text-muted"></i> {{ task.project ? task.project.name : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <UserAvatarPill :name="task.assignee ? (task.assignee.full_name || task.assignee.name) : ''" />
                                    <AiAssigneeSuggest
                                        v-if="!task.assignee_id && !task.assignee"
                                        :task="task"
                                        compact
                                        auto-save
                                        @applied="(payload) => patchTaskAssignee(task, payload.candidate)"
                                    />
                                </div>
                            </td>
                            <td>
                                <PriorityBadge :priority="task.priority" />
                            </td>
                            <td>
                                <StatusBadge :status="task.status" :workflow-statuses="getWorkflowStatusesForTask(task)" />
                            </td>
                            <td>
                                <span v-if="task.end_date || task.due_date" :style="isOverdue(task) ? 'color: #ef4444; font-weight: 600;' : 'color: #64748b;'">
                                    <i class="fa fa-calendar-o"></i> {{ formatDate(task.end_date || task.due_date) }}
                                </span>
                                <span v-else class="text-muted">-</span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a :href="`/tasks/${task.id}`" class="tp-btn tp-btn-light" style="padding: 4px 10px; font-size: 12px;" title="Xem chi tiết">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a :href="`/tasks/${task.id}/edit`" class="tp-btn tp-btn-light" style="padding: 4px 10px; font-size: 12px;" title="Chỉnh sửa">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State for Table -->
            <EmptyState
                v-else
                title="Không tìm thấy công việc nào"
                description="Không có công việc nào phù hợp với bộ lọc hiện tại hoặc chưa có dữ liệu."
                icon="fa fa-inbox"
            >
                <template #actions>
                    <button v-if="hasActiveFilters" @click="resetFilters" class="tp-btn tp-btn-light">
                        <i class="fa fa-refresh"></i> Xóa bộ lọc
                    </button>
                    <a :href="createTaskUrl" class="tp-btn tp-btn-primary">
                        <i class="fa fa-plus"></i> Tạo công việc mới
                    </a>
                </template>
            </EmptyState>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import HeaderBanner from '../common/HeaderBanner.vue';
import StatCard from '../common/StatCard.vue';
import StatusBadge from '../common/StatusBadge.vue';
import PriorityBadge from '../common/PriorityBadge.vue';
import IssueTypeBadge from '../common/IssueTypeBadge.vue';
import UserAvatarPill from '../common/UserAvatarPill.vue';
import EmptyState from '../common/EmptyState.vue';
import AiAssigneeSuggest from '../common/AiAssigneeSuggest.vue';
import AiBatchSuggest from '../common/AiBatchSuggest.vue';
import { assigneeFromCandidate } from '../../composables/useAiAssignee';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total: 0, in_progress: 0, done: 0, high_priority: 0 }) },
    createTaskUrl: { type: String, default: '/tasks/create' },
    selectedProject: { type: [String, Number], default: '' },
    selectedStatus: { type: String, default: '' },
    selectedPriority: { type: String, default: '' },
    successMessage: { type: String, default: '' },
    effectiveWorkflow: { type: Object, default: () => null },
    projectWorkflows: { type: Object, default: () => ({}) },
});

const successMessage = ref(props.successMessage);

if (successMessage.value) {
    window.setTimeout(() => {
        successMessage.value = '';
    }, 4000);
}

// Reactive Task List State
const taskList = ref([...props.tasks]);

watch(() => props.tasks, (newVal) => {
    taskList.value = [...newVal];
}, { deep: true });

// Live KPI Stats computed in real-time without page reload
const liveStats = computed(() => {
    const list = taskList.value;
    const total = list.length;
    const in_progress = list.filter(t => ['in_progress', 'doing', 'review', 'in_review'].includes(t.status)).length;
    const done = list.filter(t => ['done', 'completed', 'resolved', 'closed'].includes(t.status)).length;
    const high_priority = list.filter(t => t.priority === 'high').length;
    return {
        total,
        in_progress,
        done,
        high_priority
    };
});

const searchQuery = ref('');
const selectedProjectFilter = ref(props.selectedProject || '');
const selectedAssigneeFilter = ref('');
const selectedReporterFilter = ref('');
const selectedTypeFilter = ref('');
const selectedStatusFilter = ref(props.selectedStatus || '');
const selectedPriorityFilter = ref(props.selectedPriority || '');
const viewMode = ref('kanban'); // Default to Kanban view

// Dynamic workflow calculation based on selected project
const currentWorkflow = computed(() => {
    if (selectedProjectFilter.value && props.projectWorkflows && props.projectWorkflows[selectedProjectFilter.value]) {
        return props.projectWorkflows[selectedProjectFilter.value];
    }
    return props.effectiveWorkflow || null;
});

const currentWorkflowStatuses = computed(() => {
    const statuses = currentWorkflow.value?.statuses;
    if (statuses && Array.isArray(statuses) && statuses.length > 0) {
        return statuses.filter(s => (s.is_active ?? true) !== false);
    }
    return [
        { key: 'todo', name: 'Cần làm', icon: 'fa fa-circle-o', color: '#64748b', is_initial: true },
        { key: 'in_progress', name: 'Đang thực hiện', icon: 'fa fa-spinner', color: '#2563eb' },
        { key: 'done', name: 'Hoàn thành', icon: 'fa fa-check-circle', color: '#10b981' },
    ];
});

const columns = computed(() => {
    return currentWorkflowStatuses.value.map(s => ({
        key: s.key,
        label: s.name,
        icon: s.icon ? (s.icon.startsWith('fa') ? s.icon : `fa ${s.icon}`) : 'fa fa-circle-o',
        iconColor: s.color || '#64748b',
        badgeBg: s.color ? `${s.color}15` : '#f1f5f9',
        badgeColor: s.color || '#475569',
        category: s.category || 'in_progress',
        is_initial: Boolean(s.is_initial),
    }));
});

function getWorkflowStatusesForTask(task) {
    if (task.project_id && props.projectWorkflows && props.projectWorkflows[task.project_id]?.statuses) {
        return props.projectWorkflows[task.project_id].statuses.filter(s => (s.is_active ?? true) !== false);
    }
    return currentWorkflowStatuses.value;
}

// Dynamic options list for Assignees and Reporters
const assigneesList = computed(() => {
    const map = new Map();
    (taskList.value || []).forEach(t => {
        if (t.assignee && t.assignee.id) {
            map.set(t.assignee.id, t.assignee.full_name || t.assignee.name);
        }
    });
    return Array.from(map.entries()).map(([id, name]) => ({ id, name }));
});

const reportersList = computed(() => {
    const map = new Map();
    (taskList.value || []).forEach(t => {
        if (t.reporter && t.reporter.id) {
            map.set(t.reporter.id, t.reporter.full_name || t.reporter.name);
        }
    });
    return Array.from(map.entries()).map(([id, name]) => ({ id, name }));
});

// Drag & Drop Reactive State
const draggedTask = ref(null);
const draggedTaskId = ref(null);
const activeDropColumn = ref(null);

const hasActiveFilters = computed(() => {
    return searchQuery.value !== '' ||
        selectedProjectFilter.value !== '' ||
        selectedAssigneeFilter.value !== '' ||
        selectedReporterFilter.value !== '' ||
        selectedTypeFilter.value !== '' ||
        selectedStatusFilter.value !== '' ||
        selectedPriorityFilter.value !== '';
});

const unassignedOpenTasks = computed(() => {
    return taskList.value.filter(task => {
        if (['done', 'completed', 'resolved', 'closed'].includes(task.status)) {
            return false;
        }
        return !task.assignee_id && !task.assignee;
    });
});

const unassignedCount = computed(() => unassignedOpenTasks.value.length);

const filteredTasks = computed(() => {
    return taskList.value.filter(task => {
        // 1. Text Search (Matches task name, ID key, labels, assignee name, reporter name)
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase().trim();
            const nameMatch = task.name && task.name.toLowerCase().includes(query);
            const keyMatch = task.id && String(task.id).includes(query);
            const labelMatch = task.labels && task.labels.toLowerCase().includes(query);
            const assigneeMatch = task.assignee && (task.assignee.name || task.assignee.full_name || '').toLowerCase().includes(query);
            const reporterMatch = task.reporter && (task.reporter.name || task.reporter.full_name || '').toLowerCase().includes(query);
            if (!nameMatch && !keyMatch && !labelMatch && !assigneeMatch && !reporterMatch) {
                return false;
            }
        }

        // 2. Project Filter
        if (selectedProjectFilter.value && String(task.project_id) !== String(selectedProjectFilter.value)) {
            return false;
        }

        // 3. Assignee Filter
        if (selectedAssigneeFilter.value) {
            if (selectedAssigneeFilter.value === 'unassigned') {
                if (task.assignee_id || task.assignee) return false;
            } else {
                const targetId = String(selectedAssigneeFilter.value);
                const taskAssigneeId = String(task.assignee_id || task.assignee?.id || '');
                if (taskAssigneeId !== targetId) return false;
            }
        }

        // 4. Reporter Filter
        if (selectedReporterFilter.value) {
            const targetId = String(selectedReporterFilter.value);
            const taskReporterId = String(task.reporter_id || task.reporter?.id || '');
            if (taskReporterId !== targetId) return false;
        }

        // 5. Issue Type Filter
        if (selectedTypeFilter.value) {
            if (task.issue_type !== selectedTypeFilter.value) return false;
        }

        // 6. Priority Filter
        if (selectedPriorityFilter.value && task.priority !== selectedPriorityFilter.value) {
            return false;
        }

        // 7. Status Filter
        if (selectedStatusFilter.value) {
            const isInitial = columns.value.find(c => c.key === selectedStatusFilter.value)?.is_initial || (columns.value[0]?.key === selectedStatusFilter.value);
            if (task.status !== selectedStatusFilter.value) {
                if (!isInitial || task.status) return false;
            }
        }

        return true;
    });
});

function getTasksByStatus(group) {
    const col = columns.value.find(c => c.key === group);
    const isInitialCol = col?.is_initial || (columns.value[0]?.key === group);

    return filteredTasks.value.filter(task => {
        if (task.status === group) return true;
        // If task has no status, show in initial column
        if (!task.status && isInitialCol) return true;
        // If task has a status that is not present in the current columns, display it in initial column
        const statusExistsInCols = columns.value.some(c => c.key === task.status);
        if (!statusExistsInCols && isInitialCol) return true;
        return false;
    });
}

// Drag & Drop Handlers
function handleDragStart(event, task) {
    draggedTask.value = task;
    draggedTaskId.value = task.id;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', String(task.id));
    }
}

function handleDragEnd() {
    draggedTask.value = null;
    draggedTaskId.value = null;
    activeDropColumn.value = null;
}

function handleDragOver(event, colKey) {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
    activeDropColumn.value = colKey;
}

function handleDragEnter(event, colKey) {
    event.preventDefault();
    activeDropColumn.value = colKey;
}

function handleDragLeave(event, colKey) {
    if (activeDropColumn.value === colKey) {
        activeDropColumn.value = null;
    }
}

async function handleDrop(event, colKey) {
    event.preventDefault();
    activeDropColumn.value = null;

    if (!draggedTask.value) return;

    const task = draggedTask.value;
    const newStatus = colKey; // 'todo', 'in_progress', 'done'

    if (task.status === newStatus) return;

    const oldStatus = task.status;
    // Real-time optimistic update
    task.status = newStatus;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(`/tasks/${task.id}/move`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            task.status = oldStatus;
            if (data.gate_type === 'approval_gate') {
                const sig = prompt(data.message + '\n\nNhập chữ ký duyệt của Tech Lead hoặc Khách hàng để tiếp tục:');
                if (sig && sig.trim()) {
                    await sendMoveWithPayload(task, newStatus, { approval_signature: sig.trim() }, oldStatus);
                }
            } else if (data.gate_type === 'validation_rule' && data.required_action === 'worklog') {
                const hours = prompt(data.message + '\n\nNhập số giờ làm việc thực tế (worklog):', '1.0');
                if (hours && parseFloat(hours) > 0) {
                    await sendMoveWithPayload(task, newStatus, { worklog_hours: parseFloat(hours) }, oldStatus);
                }
            } else if (data.gate_type === 'validation_rule' && data.required_action === 'test_result') {
                const tr = prompt(data.message + '\n\nGhi nhận kết quả kiểm thử (Test Result / Passed):', 'Passed');
                if (tr && tr.trim()) {
                    await sendMoveWithPayload(task, newStatus, { test_status: 'passed', test_result: tr.trim() }, oldStatus);
                }
            } else {
                alert(data.message || 'Không thể chuyển trạng thái công việc.');
            }
        }
    } catch (err) {
        console.error('Error moving task:', err);
        task.status = oldStatus;
    }
}

async function sendMoveWithPayload(task, newStatus, payload, oldStatus) {
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(`/tasks/${task.id}/move`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus, ...payload })
        });
        const data = await res.json();
        if (data.success) {
            task.status = newStatus;
            alert(`Đã chuyển trạng thái sang "${newStatus}" thành công.`);
        } else {
            task.status = oldStatus;
            alert(data.message || 'Không thể chuyển trạng thái.');
        }
    } catch (e) {
        task.status = oldStatus;
    }
}

function resetFilters() {
    searchQuery.value = '';
    selectedProjectFilter.value = props.selectedProject || '';
    selectedAssigneeFilter.value = '';
    selectedReporterFilter.value = '';
    selectedTypeFilter.value = '';
    selectedStatusFilter.value = '';
    selectedPriorityFilter.value = '';
}

function goToTask(id) {
    window.location.href = `/tasks/${id}`;
}

function patchTaskAssignee(task, candidate) {
    task.assignee_id = candidate.user_id;
    task.assignee = assigneeFromCandidate(candidate);
}

function applyBatchResults(assignments) {
    assignments.forEach(({ taskId, candidate }) => {
        const task = taskList.value.find(item => item.id === taskId);
        if (task) {
            patchTaskAssignee(task, candidate);
        }
    });
}

function applyAiTestReset(tasks) {
    (tasks || []).forEach((fresh) => {
        const task = taskList.value.find(item => item.id === fresh.id);
        if (task) {
            task.assignee_id = null;
            task.assignee = null;
        }
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function isOverdue(task) {
    const dateStr = task.end_date || task.due_date;
    if (!dateStr) return false;
    if (['done', 'completed', 'resolved', 'closed'].includes(task.status)) return false;
    const due = new Date(dateStr);
    const now = new Date();
    return due < now;
}
</script>
