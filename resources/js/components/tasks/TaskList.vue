<template>
    <div class="task-index-vue">
        <!-- Page Header Banner (Optional header if not in project context) -->
        <HeaderBanner
            v-if="!selectedProjectFilter"
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
                <div class="edit-select-wrapper" style="min-width: 140px;">
                    <select v-model="selectedStatusFilter" class="edit-select">
                        <option value="">📌 Trạng thái: Tất cả</option>
                        <option value="todo">Cần làm</option>
                        <option value="in_progress">Đang làm</option>
                        <option value="done">Hoàn thành</option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Reset Filter Button -->
                <button v-if="hasActiveFilters" class="tp-btn tp-btn-light btn-sm ms-1" @click="resetFilters" title="Xóa tất cả bộ lọc">
                    <i class="fa fa-times text-danger"></i> Xóa lọc
                </button>
            </div>

            <!-- View Toggle Buttons (Kanban / Table) -->
            <div class="tp-view-toggle ms-auto" style="display: flex; align-items: center; gap: 8px;">
                <AiBatchSuggest :tasks="unassignedOpenTasks" :project-id="selectedProjectFilter || null" @applied="applyBatchResults" @reset="applyAiTestReset" />
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

        <div v-if="unassignedCount > 0" class="ai-unassigned-banner">
            <span>
                <i class="fa fa-magic" style="color: #8b5cf6; margin-right: 6px;"></i>
                Có <strong>{{ unassignedCount }}</strong> công việc chưa phân công. Bấm
                <strong>AI gợi ý tất cả</strong> để đề xuất người cho cả danh sách, hoặc nút
                <strong>AI</strong> trên từng thẻ.
            </span>
            <button type="button" class="ai-suggest-btn ai-suggest-btn-compact" @click="selectedAssigneeFilter = 'unassigned'">
                Lọc chưa phân công
            </button>
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
                                <StatusBadge :status="task.status" />
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
});

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

const columns = [
    { key: 'todo', label: 'Cần làm', icon: 'fa fa-circle-o', iconColor: '#64748b', badgeBg: '#f1f5f9', badgeColor: '#475569' },
    { key: 'in_progress', label: 'Đang thực hiện', icon: 'fa fa-spinner', iconColor: '#2563eb', badgeBg: '#eff6ff', badgeColor: '#2563eb' },
    { key: 'done', label: 'Hoàn thành', icon: 'fa fa-check-circle', iconColor: '#10b981', badgeBg: '#d1fae5', badgeColor: '#059669' },
];

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
            const s = selectedStatusFilter.value;
            if (s === 'todo' && !['todo', 'backlog', 'open', 'new'].includes(task.status)) return false;
            if (s === 'in_progress' && !['in_progress', 'doing', 'review', 'in_review'].includes(task.status)) return false;
            if (s === 'done' && !['done', 'completed', 'resolved', 'closed'].includes(task.status)) return false;
        }

        return true;
    });
});

function getTasksByStatus(group) {
    return filteredTasks.value.filter(task => {
        if (group === 'todo') return ['todo', 'backlog', 'open', 'new'].includes(task.status) || !task.status;
        if (group === 'in_progress') return ['in_progress', 'doing', 'review', 'in_review'].includes(task.status);
        if (group === 'done') return ['done', 'completed', 'resolved', 'closed'].includes(task.status);
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
        if (!data.success) {
            task.status = oldStatus;
            alert('Không thể chuyển trạng thái công việc.');
        }
    } catch (err) {
        console.error('Error moving task:', err);
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
