<template>
    <div class="project-dashboard-vue">
        <!-- 1. HEADER -->
        <div class="pd-header mb-3">
            <div class="pd-header-left">
                <div class="pd-breadcrumb mb-1">
                    <a href="/projects">Dự án</a>
                    <span>/</span>
                    <span>{{ project.name }}</span>
                </div>
                <div class="pd-title-row">
                    <h1 class="pd-title">{{ project.name }}</h1>
                    <span class="pd-project-key">{{ project.project_code || 'PROJ' }}</span>
                    <span class="pd-project-status">{{ project.status || 'ACTIVE' }}</span>
                    <a :href="`/projects/${project.id}/edit`" class="pd-settings-btn" title="Cài đặt dự án">
                        <i class="fa fa-cog"></i>
                    </a>
                </div>
                <div class="pd-meta mt-2">
                    <span class="pd-meta-item">
                        <i class="fa fa-calendar"></i>
                        {{ formatDate(project.start_date) }} - {{ formatDate(project.end_date) }}
                    </span>
                    <span class="pd-meta-item">
                        <i class="fa fa-flag"></i>
                        {{ priorityLabel(project.priority) }}
                    </span>
                    <span class="pd-meta-item">
                        <i class="fa fa-money"></i>
                        {{ project.budget ? formatNumber(project.budget) + ' VND' : 'Chưa đặt' }}
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <AiBatchSuggest :tasks="unassignedOpenTasks" :project-id="project.id" @applied="applyBatchResults" @reset="applyAiTestReset" />
                <a :href="`/tasks/create?project_id=${project.id}`" class="tp-btn tp-btn-primary">
                    <i class="fa fa-plus-circle" style="color: #ffffff !important;"></i>
                    <span>Tạo công việc mới</span>
                </a>
            </div>
        </div>

        <!-- 2. PROJECT SUB-NAV -->
        <div class="pd-project-nav mb-4">
            <a href="/projects" class="pd-nav-item">Dự án</a>
            <a :href="`/projects/${project.id}`" class="pd-nav-item" :class="{ active: currentTab === 'overview' }" @click.prevent="currentTab = 'overview'">Tổng quan</a>
            <a :href="`/projects/${project.id}/board`" class="pd-nav-item" :class="{ active: currentTab === 'board' }" @click.prevent="currentTab = 'board'">Bảng (Board)</a>
            <a :href="`/projects/${project.id}/members`" class="pd-nav-item">Thành viên</a>
            <a :href="`/projects/${project.id}/roles`" class="pd-nav-item">Vai trò &amp; Quyền</a>
            <a :href="`/projects/${project.id}/sprints`" class="pd-nav-item">Backlog / Sprint</a>
            <a :href="`/sprints?project_id=${project.id}`" class="pd-nav-item">Backlog / Sprint</a>
            <a :href="`/projects/${project.id}/timeline`" class="pd-nav-item">Timeline</a>
            <a :href="`/projects/${project.id}/roadmap`" class="pd-nav-item">Roadmap</a>
            <a :href="`/projects/${project.id}/reports`" class="pd-nav-item">Báo cáo</a>
            <a :href="`#activity`" class="pd-nav-item" :class="{ active: currentTab === 'activity' }" @click.prevent="currentTab = 'activity'">Nhật ký hoạt động</a>
        </div>

        <!-- 3. REAL-TIME REACTIVE KPI STATS ROW (Updates instantly on drag-and-drop) -->
        <div class="pd-stats mb-4">
            <div class="pd-stat-card">
                <div class="pd-stat-label"><i class="fa fa-list-alt text-primary me-1"></i>Tổng công việc</div>
                <div class="pd-stat-value">{{ stats.total }}</div>
            </div>
            <div class="pd-stat-card pd-stat-done">
                <div class="pd-stat-label"><i class="fa fa-check-circle text-success me-1"></i>Hoàn thành</div>
                <div class="pd-stat-value">{{ stats.done }}</div>
                <div class="pd-stat-percent">{{ stats.total ? Math.round(stats.done / stats.total * 100) : 0 }}%</div>
            </div>
            <div class="pd-stat-card pd-stat-progress">
                <div class="pd-stat-label"><i class="fa fa-spinner text-info me-1"></i>Đang thực hiện</div>
                <div class="pd-stat-value">{{ stats.inProgress }}</div>
                <div class="pd-stat-percent">{{ stats.total ? Math.round(stats.inProgress / stats.total * 100) : 0 }}%</div>
            </div>
            <div class="pd-stat-card pd-stat-overdue">
                <div class="pd-stat-label"><i class="fa fa-exclamation-triangle text-danger me-1"></i>Ưu tiên cao / Quá hạn</div>
                <div class="pd-stat-value">{{ stats.highOrOverdue }}</div>
                <div class="pd-stat-percent">{{ stats.total ? Math.round(stats.highOrOverdue / stats.total * 100) : 0 }}%</div>
            </div>
        </div>

        <!-- 4. OVERVIEW SECTION (Only visible on Overview tab) -->
        <div v-if="currentTab === 'overview'" class="pd-grid mb-5">
            <!-- LEFT COLUMN -->
            <div class="pd-main">
                <!-- PROGRESS BAR CARD -->
                <div class="pd-jira-card">
                    <div class="pd-jira-card-header">
                        <h2 class="pd-jira-card-title">
                            <i class="fa fa-line-chart text-primary me-2"></i>Tiến độ dự án tổng thể
                        </h2>
                    </div>
                    <div class="pd-jira-card-body">
                        <div class="pd-progress-bar mb-2">
                            <div class="pd-progress-fill" :style="{ width: progressPercent + '%' }"></div>
                        </div>
                        <div class="pd-progress-labels">
                            <span>Start {{ formatDate(project.start_date) }}</span>
                            <span>Today {{ todayLabel }}</span>
                            <span>End {{ formatDate(project.end_date) }}</span>
                        </div>
                        <div class="pd-progress-percent mt-2">{{ progressPercent }}%</div>
                    </div>
                </div>

                <!-- RECENT TASKS CARD -->
                <div class="pd-jira-card">
                    <div class="pd-jira-card-header d-flex justify-content-between align-items-center">
                        <h2 class="pd-jira-card-title">
                            <i class="fa fa-tasks text-primary me-2"></i>Công việc gần đây
                        </h2>
                        <button class="tp-btn tp-btn-light btn-xs" @click="currentTab = 'board'">
                            Mở Bảng Kanban <i class="fa fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                    <div class="pd-jira-card-body">
                        <div v-if="recentTasks.length" class="pd-task-list">
                            <div v-for="task in recentTasks" :key="task.id" class="pd-task-row">
                                <div class="pd-task-key">{{ task.project ? task.project.project_code : 'TP' }}-{{ task.id }}</div>
                                <div class="pd-task-name">
                                    <a :href="`/tasks/${task.id}`" class="text-dark font-weight-bold text-decoration-none">
                                        {{ task.name }}
                                    </a>
                                </div>
                                <span class="pd-priority-badge" :class="'pd-priority-' + (task.priority || 'low')">
                                    {{ priorityLabel(task.priority) }}
                                </span>
                                <span class="pd-task-assignee">{{ task.assignee?.name || 'Chưa gán' }}</span>
                                <span class="pd-task-date">{{ formatDate(task.due_date) }}</span>
                            </div>
                        </div>
                        <div v-else class="pd-empty">Chưa có task nào trong dự án này.</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="pd-sidebar">
                <!-- AI INSIGHTS -->
                <div class="pd-jira-card">
                    <div class="pd-jira-card-header">
                        <h2 class="pd-jira-card-title">
                            <i class="fa fa-magic text-warning me-2"></i>AI gợi ý phân công
                        </h2>
                    </div>
                    <div class="pd-jira-card-body">
                        <div class="pd-insight-item">
                            <i class="fa fa-shield text-primary"></i>
                            <span>{{ risks.length }} rủi ro cần chú ý</span>
                        </div>
                        <div v-if="risks.length" class="pd-risk-list">
                            <div v-for="risk in risks" :key="risk" class="pd-risk-item">
                                • {{ risk }}
                            </div>
                        </div>
                        <div v-else class="pd-empty-small">Không có rủi ro đáng kể.</div>

                        <div class="pd-insight-item mt-3">
                            <i class="fa fa-user-times" style="color: #8b5cf6;"></i>
                            <span>{{ unassignedOpenTasks.length }} công việc chưa phân công</span>
                        </div>
                        <div v-if="unassignedOpenTasks.length" class="ai-insight-unassigned">
                            <div v-for="task in unassignedOpenTasks.slice(0, 5)" :key="task.id" class="ai-insight-row">
                                <a :href="`/tasks/${task.id}`" class="text-dark text-decoration-none" style="min-width: 0;">
                                    <div class="ai-home-item-title text-truncate">{{ task.name }}</div>
                                    <div class="ai-home-item-meta">{{ priorityLabel(task.priority) }}</div>
                                </a>
                                <AiAssigneeSuggest
                                    :task="task"
                                    compact
                                    auto-save
                                    @applied="(payload) => patchTaskAssignee(task, payload.candidate)"
                                />
                            </div>
                        </div>
                        <div v-else class="pd-empty-small">Mọi công việc đang mở đều đã có người nhận.</div>
                    </div>
                </div>

                <!-- WORK DISTRIBUTION -->
                <div class="pd-jira-card">
                    <div class="pd-jira-card-header">
                        <h2 class="pd-jira-card-title">
                            <i class="fa fa-pie-chart text-primary me-2"></i>Phân bố công việc
                        </h2>
                    </div>
                    <div class="pd-jira-card-body">
                        <div v-if="workDistribution.length" class="pd-dist-list">
                            <div v-for="item in workDistribution" :key="item.label" class="pd-dist-row">
                                <span class="pd-dist-label">{{ item.label }}</span>
                                <span class="pd-dist-value">{{ item.count }}</span>
                                <div class="pd-dist-bar">
                                    <div class="pd-dist-fill" :style="{ width: item.percent + '%' }"></div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="pd-empty-small">Chưa có dữ liệu.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. KANBAN BOARD SECTION (Only visible when 'Bảng (Board)' tab is selected) -->
        <div v-if="currentTab === 'board'" class="pd-kanban-section mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="m-0 font-weight-bold font-size-18 text-dark">
                    <i class="fa fa-columns text-primary me-2"></i>Bảng Công việc Kanban (Kéo thả trực tiếp)
                </h3>
                <span class="text-muted small">
                    <i class="fa fa-info-circle me-1"></i>Kéo thả thẻ công việc giữa các cột để đổi trạng thái. Thống kê ở trên sẽ tự nảy số ngay lập tức!
                </span>
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

                <!-- View Toggle Buttons -->
                <div class="tp-view-toggle ms-auto" style="display: flex; align-items: center; gap: 8px;">
                    <AiBatchSuggest :tasks="unassignedOpenTasks" :project-id="project.id" @applied="applyBatchResults" @reset="applyAiTestReset" />
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

            <!-- KANBAN BOARD WITH HTML5 DRAG & DROP -->
            <p v-if="viewMode === 'kanban'" class="tp-mobile-scroll-hint"><i class="fa fa-arrows-h"></i> Vuốt ngang để xem các cột</p>
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
                            <a :href="`/tasks/create?project_id=${project.id}&status=${col.key}`" class="kanban-add-btn" title="Tạo nhanh công việc">
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
                                    <span class="task-key-tag">{{ project.project_code || 'TP' }}-{{ task.id }}</span>
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
                                    {{ project.name }}
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

                        <!-- Column Empty Indicator -->
                        <div v-if="getTasksByStatus(col.key).length === 0" class="tp-kanban-empty">
                            <i class="fa fa-hand-o-down text-muted font-size-18 mb-1"></i>
                            <span>Kéo thả công việc vào đây</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE VIEW -->
            <div v-else class="tp-table-card mt-3">
                <table class="tp-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Loại</th>
                            <th>Công việc</th>
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
                                <span v-if="task.due_date || task.end_date" class="text-muted">
                                    <i class="fa fa-calendar-o"></i> {{ formatDate(task.due_date || task.end_date) }}
                                </span>
                                <span v-else class="text-muted">-</span>
                            </td>
                            <td style="text-align: right;">
                                <a :href="`/tasks/${task.id}`" class="tp-btn tp-btn-light" style="padding: 4px 10px; font-size: 12px;">
                                    <i class="fa fa-eye"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6. PROJECT ACTIVITY FEED -->
        <div v-if="currentTab === 'activity'" class="pd-activity-section mt-4">
            <div class="pd-jira-card">
                <div class="pd-jira-card-header d-flex justify-content-between align-items-center">
                    <h2 class="pd-jira-card-title">
                        <i class="fa fa-history text-primary me-2"></i>Nhật ký hoạt động của dự án
                    </h2>
                    <div class="d-flex gap-2">
                        <select v-model="activityFilter" class="edit-input" style="min-width: 180px;">
                            <option value="">Tất cả hoạt động</option>
                            <option value="created">Tạo mới</option>
                            <option value="updated">Cập nhật</option>
                            <option value="deleted">Đã xóa</option>
                            <option value="invited">Mời thành viên</option>
                            <option value="removed">Xóa thành viên</option>
                            <option value="commented">Bình luận</option>
                            <option value="comment_edited">Sửa bình luận</option>
                            <option value="comment_deleted">Xóa bình luận</option>
                            <option value="logged_time">Ghi nhận giờ</option>
                            <option value="archived">Lưu trữ</option>
                        </select>
                    </div>
                </div>
                <div class="pd-jira-card-body">
                    <div v-if="filteredActivities.length === 0" class="pd-empty-small">
                        <i class="fa fa-clock-o me-1"></i> Chưa có hoạt động nào được ghi nhận.
                    </div>
                    <ul v-else class="pd-activity-list">
                        <li v-for="item in filteredActivities" :key="item.id" class="pd-activity-item">
                            <div class="pd-activity-icon" :class="'pd-activity-' + item.action">
                                <i :class="activityIcon(item.action)"></i>
                            </div>
                            <div class="pd-activity-body">
                                <div class="pd-activity-text">
                                    <strong>{{ item.user?.name || 'Hệ thống' }}</strong>
                                    {{ activityVerb(item) }}
                                    <span v-if="item.subject_label" class="pd-activity-subject">{{ item.subject_label }}</span>
                                    <span v-if="item.meta" class="pd-activity-meta"> · {{ item.meta }}</span>
                                </div>
                                <div v-if="item.changes && Object.keys(item.changes).length" class="pd-activity-changes">
                                    <span v-for="(change, field) in item.changes" :key="field" class="pd-activity-change">
                                        <span class="pd-activity-field">{{ field }}</span>:
                                        <span class="pd-activity-old">{{ change.old ?? '∅' }}</span>
                                        →
                                        <span class="pd-activity-new">{{ change.new ?? '∅' }}</span>
                                    </span>
                                </div>
                                <div class="pd-activity-time">{{ formatRelativeTime(item.created_at) }}</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, watch } from 'vue';
import StatusBadge from '../common/StatusBadge.vue';
import PriorityBadge from '../common/PriorityBadge.vue';
import IssueTypeBadge from '../common/IssueTypeBadge.vue';
import UserAvatarPill from '../common/UserAvatarPill.vue';
import AiAssigneeSuggest from '../common/AiAssigneeSuggest.vue';
import AiBatchSuggest from '../common/AiBatchSuggest.vue';
import { assigneeFromCandidate } from '../../composables/useAiAssignee';
import { subscribeToProject } from '../../realtime';

const props = defineProps({
    project: { type: Object, required: true },
    tasks: { type: Array, default: () => [] },
    comments: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    initialTab: { type: String, default: 'overview' },
});

const currentTab = ref(props.initialTab || 'overview');
const activityFilter = ref('');
const searchQuery = ref('');
const selectedAssigneeFilter = ref('');
const selectedReporterFilter = ref('');
const selectedTypeFilter = ref('');
const selectedStatusFilter = ref('');
const selectedPriorityFilter = ref('');
const viewMode = ref('kanban');

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

const hasActiveFilters = computed(() => {
    return searchQuery.value !== '' ||
        selectedAssigneeFilter.value !== '' ||
        selectedReporterFilter.value !== '' ||
        selectedTypeFilter.value !== '' ||
        selectedStatusFilter.value !== '' ||
        selectedPriorityFilter.value !== '';
});

// Reactive Tasks List
const taskList = ref([...props.tasks]);
let leaveRealtime = () => {};
onMounted(() => { leaveRealtime = subscribeToProject(props.project?.id, ({ task }) => { const existing = taskList.value.findIndex((item) => item.id === task.id); if (existing >= 0) taskList.value[existing] = { ...taskList.value[existing], ...task }; }); });
onUnmounted(() => leaveRealtime());

watch(() => props.tasks, (newVal) => {
    taskList.value = [...newVal];
}, { deep: true });

// Live KPI Stats computed reactively in real-time
const today = new Date();

const stats = computed(() => {
    const all = taskList.value || [];
    const total = all.length;
    const done = all.filter(t => ['done', 'completed', 'resolved'].includes(t.status)).length;
    const inProgress = all.filter(t => ['in_progress', 'doing', 'review'].includes(t.status)).length;
    const highOrOverdue = all.filter(t => {
        const isHigh = t.priority === 'high';
        const isOverdue = t.due_date ? new Date(t.due_date) < today && t.status !== 'done' : false;
        return isHigh || isOverdue;
    }).length;

    return {
        total,
        done,
        inProgress,
        highOrOverdue
    };
});

const progressPercent = computed(() => {
    if (!stats.value.total) return 0;
    return Math.round((stats.value.done / stats.value.total) * 100);
});

const todayLabel = computed(() => {
    return `${String(today.getDate()).padStart(2, '0')}/${String(today.getMonth() + 1).padStart(2, '0')}`;
});

const recentTasks = computed(() => {
    return taskList.value.slice().sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0)).slice(0, 5);
});

const unassignedOpenTasks = computed(() => {
    return taskList.value.filter(task => {
        if (['done', 'completed', 'resolved', 'closed'].includes(task.status)) {
            return false;
        }
        return !task.assignee_id && !task.assignee;
    });
});

const risks = computed(() => {
    const list = [];
    const overdueList = taskList.value.filter(t => t.due_date ? new Date(t.due_date) < today && t.status !== 'done' : false);
    if (overdueList.length > 0) {
        list.push(`${overdueList.length} công việc có nguy cơ trễ hạn`);
    }
    const highProgress = taskList.value.filter(t => t.priority === 'high' && t.status !== 'done').length;
    if (highProgress > 0) {
        list.push(`${highProgress} công việc ưu tiên cao đang cần xử lý khẩn cấp`);
    }
    return list;
});

const workDistribution = computed(() => {
    const map = { backend: 'Backend', frontend: 'Frontend', ai: 'AI' };
    const counts = { backend: 0, frontend: 0, ai: 0 };
    const total = taskList.value.length;

    taskList.value.forEach(t => {
        const text = ((t.name || '') + ' ' + (t.labels || '')).toLowerCase();
        if (text.includes('backend') || text.includes('api') || text.includes('server')) counts.backend++;
        else if (text.includes('frontend') || text.includes('ui') || text.includes('css')) counts.frontend++;
        else if (text.includes('ai') || text.includes('ml') || text.includes('model')) counts.ai++;
    });

    return Object.keys(map).map(k => ({
        label: map[k],
        count: counts[k],
        percent: total ? Math.round((counts[k] / total) * 100) : 0
    }));
});

// Drag & Drop Kanban State
const draggedTask = ref(null);
const draggedTaskId = ref(null);
const activeDropColumn = ref(null);

const columns = [
    { key: 'todo', label: 'Cần làm', icon: 'fa fa-circle-o', iconColor: '#64748b', badgeBg: '#f1f5f9', badgeColor: '#475569' },
    { key: 'in_progress', label: 'Đang thực hiện', icon: 'fa fa-spinner', iconColor: '#2563eb', badgeBg: '#eff6ff', badgeColor: '#2563eb' },
    { key: 'done', label: 'Hoàn thành', icon: 'fa fa-check-circle', iconColor: '#10b981', badgeBg: '#d1fae5', badgeColor: '#059669' },
];

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

        // 2. Assignee Filter
        if (selectedAssigneeFilter.value) {
            if (selectedAssigneeFilter.value === 'unassigned') {
                if (task.assignee_id || task.assignee) return false;
            } else {
                const targetId = String(selectedAssigneeFilter.value);
                const taskAssigneeId = String(task.assignee_id || task.assignee?.id || '');
                if (taskAssigneeId !== targetId) return false;
            }
        }

        // 3. Reporter Filter
        if (selectedReporterFilter.value) {
            const targetId = String(selectedReporterFilter.value);
            const taskReporterId = String(task.reporter_id || task.reporter?.id || '');
            if (taskReporterId !== targetId) return false;
        }

        // 4. Issue Type Filter
        if (selectedTypeFilter.value) {
            if (task.issue_type !== selectedTypeFilter.value) return false;
        }

        // 5. Priority Filter
        if (selectedPriorityFilter.value && task.priority !== selectedPriorityFilter.value) {
            return false;
        }

        // 6. Status Filter
        if (selectedStatusFilter.value) {
            const s = selectedStatusFilter.value;
            if (s === 'todo' && !['todo', 'backlog', 'open', 'new'].includes(task.status)) return false;
            if (s === 'in_progress' && !['in_progress', 'doing', 'review'].includes(task.status)) return false;
            if (s === 'done' && !['done', 'completed', 'resolved'].includes(task.status)) return false;
        }

        return true;
    });
});

function getTasksByStatus(group) {
    return filteredTasks.value.filter(task => {
        if (group === 'todo') return ['todo', 'backlog', 'open', 'new'].includes(task.status) || !task.status;
        if (group === 'in_progress') return ['in_progress', 'doing', 'review'].includes(task.status);
        if (group === 'done') return ['done', 'completed', 'resolved'].includes(task.status);
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
    const newStatus = colKey;

    if (task.status === newStatus) return;

    const oldStatus = task.status;
    // Real-time optimistic update: trigger stats and progress re-calculation immediately!
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
    if (!dateStr) return 'Chưa đặt';
    const d = new Date(dateStr);
    if (isNaN(d)) return 'Chưa đặt';
    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}`;
}

function formatNumber(val) {
    if (!val && val !== 0) return '0';
    return Number(val).toLocaleString('vi-VN');
}

function priorityLabel(p) {
    return { high: 'Cao', medium: 'Trung bình', low: 'Thấp' }[p] || p || 'Thấp';
}

const filteredActivities = computed(() => {
    if (!activityFilter.value) return props.activities;
    return props.activities.filter(a => a.action === activityFilter.value);
});

const ACTIVITY_VERBS = {
    created: 'đã tạo',
    updated: 'đã cập nhật',
    deleted: 'đã xóa',
    invited: 'đã mời',
    removed: 'đã xóa khỏi dự án',
    commented: 'đã bình luận về',
    comment_edited: 'đã chỉnh sửa bình luận trên',
    comment_deleted: 'đã xóa bình luận trên',
    logged_time: 'đã ghi nhận',
    archived: 'đã lưu trữ',
    restored: 'đã khôi phục',
};

const ACTIVITY_ICONS = {
    created: 'fa fa-plus-circle text-success',
    updated: 'fa fa-pencil text-primary',
    deleted: 'fa fa-trash text-danger',
    invited: 'fa fa-user-plus text-info',
    removed: 'fa fa-user-times text-danger',
    commented: 'fa fa-comment text-primary',
    comment_edited: 'fa fa-commenting text-warning',
    comment_deleted: 'fa fa-comment-o text-muted',
    logged_time: 'fa fa-clock-o text-success',
    archived: 'fa fa-archive text-muted',
    restored: 'fa fa-undo text-info',
};

function activityVerb(item) {
    return ACTIVITY_VERBS[item.action] || item.action;
}

function activityIcon(action) {
    return ACTIVITY_ICONS[action] || 'fa fa-circle text-muted';
}

function formatRelativeTime(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (isNaN(date)) return '';
    const diffMs = Date.now() - date.getTime();
    const sec = Math.round(diffMs / 1000);
    if (sec < 60) return 'vừa xong';
    const min = Math.round(sec / 60);
    if (min < 60) return `${min} phút trước`;
    const hr = Math.round(min / 60);
    if (hr < 24) return `${hr} giờ trước`;
    const day = Math.round(hr / 24);
    if (day < 7) return `${day} ngày trước`;
    return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
}
</script>
