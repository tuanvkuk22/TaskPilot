<template>
    <div class="project-calendar-vue">
        <!-- Standard Project Header -->
        <div class="pd-header mb-3">
            <div class="pd-header-left">
                <div class="pd-breadcrumb mb-1">
                    <a href="/projects">Dự án</a>
                    <span>/</span>
                    <a :href="`/projects/${project.id}`">{{ project.name }}</a>
                    <span>/</span>
                    <span class="pd-breadcrumb-active">Lịch biểu &amp; Tiến độ (Calendar)</span>
                </div>
                <div class="pd-title-row">
                    <h1 class="pd-title m-0">{{ project.name }} / Lịch biểu</h1>
                    <span class="pd-project-key">{{ project.project_code || 'PROJ' }}</span>
                    <span class="pd-project-status">{{ (project.status || 'active').toUpperCase() }}</span>
                    <span class="tp-badge tp-badge-workflow ms-1">
                        <i class="fa fa-sitemap me-1"></i> {{ (project.project_type || 'software').toUpperCase() }} WORKFLOW
                    </span>
                    <a :href="`/projects/${project.id}/edit`" class="pd-settings-btn" title="Cài đặt dự án &amp; Workflow">
                        <i class="fa fa-cog"></i>
                    </a>
                </div>
            </div>
            <div class="pd-header-actions">
                <a :href="`/tasks/create?project_id=${project.id}`" class="tp-btn tp-btn-primary">
                    <i class="fa fa-plus-circle"></i>
                    <span>Tạo công việc mới</span>
                </a>
            </div>
        </div>

        <!-- Project Sub-Nav Bar -->
        <div class="pd-project-nav mb-4">
            <a :href="`/projects`" class="pd-nav-item">Dự án</a>
            <a :href="`/projects/${project.id}`" class="pd-nav-item">Tổng quan</a>
            <a :href="`/projects/${project.id}/board`" class="pd-nav-item">Bảng</a>
            <a :href="`/projects/${project.id}/members`" class="pd-nav-item">Thành viên</a>
            <a :href="`/projects/${project.id}/roles`" class="pd-nav-item">Vai trò &amp; Quyền</a>
            <a :href="`/projects/${project.id}/sprints`" class="pd-nav-item">Backlog / Sprint</a>
            <a :href="`/projects/${project.id}/timeline`" class="pd-nav-item">Timeline</a>
            <a :href="`/projects/${project.id}/roadmap`" class="pd-nav-item active">
                <i class="fa fa-calendar me-1"></i> Lịch biểu (Calendar)
            </a>
        </div>

        <!-- KPI Metric Cards for Calendar -->
        <div class="tp-stats-grid mb-4">
            <div class="tp-stat-card">
                <div class="tp-stat-header">
                    <span class="tp-stat-label">Tổng số công việc</span>
                    <i class="fa fa-list-alt text-muted"></i>
                </div>
                <div class="tp-stat-value">{{ tasksList.length }}</div>
                <span class="tp-stat-hint">Toàn bộ công việc trong dự án</span>
            </div>
            <div class="tp-stat-card">
                <div class="tp-stat-header">
                    <span class="tp-stat-label">Đã lên lịch kỳ này</span>
                    <i class="fa fa-calendar text-primary"></i>
                </div>
                <div class="tp-stat-value text-primary">{{ monthTasksCount }}</div>
                <span class="tp-stat-hint">{{ monthTitle }}</span>
            </div>
            <div class="tp-stat-card">
                <div class="tp-stat-header">
                    <span class="tp-stat-label">Đã hoàn thành</span>
                    <i class="fa fa-check-circle text-success"></i>
                </div>
                <div class="tp-stat-value text-success">{{ completedTasksCount }}</div>
                <span class="tp-stat-hint">Trạng thái Done</span>
            </div>
            <div class="tp-stat-card">
                <div class="tp-stat-header">
                    <span class="tp-stat-label">Quá hạn (Overdue)</span>
                    <i class="fa fa-exclamation-triangle text-danger"></i>
                </div>
                <div class="tp-stat-value text-danger">{{ overdueTasksCount }}</div>
                <span class="tp-stat-hint">Cần xử lý gấp</span>
            </div>
        </div>

        <!-- Smart Notice: If viewing a month with 0 tasks but project has tasks -->
        <div v-if="monthTasksCount === 0 && nearestTaskMonth" class="tp-cal-jump-banner mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fa fa-info-circle text-primary font-size-16"></i>
                <span>Tháng <strong>{{ currentDate.getMonth() + 1 }}/{{ currentDate.getFullYear() }}</strong> chưa có công việc. Dự án có <strong>{{ tasksWithDatesCount }}</strong> công việc ở kỳ khác (gần nhất: <strong>Tháng {{ nearestTaskMonth.getMonth() + 1 }}/{{ nearestTaskMonth.getFullYear() }}</strong>).</span>
            </div>
            <button type="button" class="tp-btn tp-btn-sm tp-btn-primary" @click="jumpToNearestTaskMonth">
                <i class="fa fa-arrow-circle-right me-1"></i> Chuyển tới kỳ có việc
            </button>
        </div>

        <!-- Calendar Control Bar -->
        <div class="tp-cal-control-bar mb-3">
            <div class="tp-cal-nav-group">
                <!-- Navigation Buttons -->
                <div class="tp-btn-group">
                    <button type="button" class="tp-btn tp-btn-sm tp-btn-outline" @click="prevPeriod" title="Kỳ trước">
                        <i class="fa fa-chevron-left"></i>
                    </button>
                    <button type="button" class="tp-btn tp-btn-sm tp-btn-outline font-weight-bold" @click="goToToday">
                        Hôm nay
                    </button>
                    <button type="button" class="tp-btn tp-btn-sm tp-btn-outline" @click="nextPeriod" title="Kỳ sau">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                </div>

                <!-- Month & Year Selectors for instant jumping -->
                <div class="tp-date-jump-selectors">
                    <select v-model="selectedMonthIndex" class="tp-select tp-select-compact" @change="onMonthSelectChange">
                        <option v-for="(m, idx) in monthNames" :key="idx" :value="idx">
                            {{ m }}
                        </option>
                    </select>

                    <select v-model="selectedYear" class="tp-select tp-select-compact" @change="onYearSelectChange">
                        <option v-for="y in availableYears" :key="y" :value="y">
                            Năm {{ y }}
                        </option>
                    </select>
                </div>

                <!-- Current Month / Year Display Title -->
                <h2 class="tp-cal-month-title m-0">
                    <i class="fa fa-calendar text-primary me-2"></i>
                    {{ currentPeriodLabel }}
                </h2>
            </div>

            <!-- View Switcher & Filters -->
            <div class="tp-cal-mode-group">
                <!-- View Mode Buttons -->
                <div class="tp-btn-group">
                    <button
                        type="button"
                        class="tp-btn tp-btn-sm"
                        :class="viewMode === 'month' ? 'tp-btn-primary' : 'tp-btn-outline'"
                        @click="viewMode = 'month'"
                    >
                        <i class="fa fa-th me-1"></i> Tháng
                    </button>
                    <button
                        type="button"
                        class="tp-btn tp-btn-sm"
                        :class="viewMode === 'week' ? 'tp-btn-primary' : 'tp-btn-outline'"
                        @click="viewMode = 'week'"
                    >
                        <i class="fa fa-columns me-1"></i> Tuần
                    </button>
                    <button
                        type="button"
                        class="tp-btn tp-btn-sm"
                        :class="viewMode === 'agenda' ? 'tp-btn-primary' : 'tp-btn-outline'"
                        @click="viewMode = 'agenda'"
                    >
                        <i class="fa fa-list me-1"></i> Lịch trình
                    </button>
                </div>

                <!-- Toggle Unscheduled Drawer -->
                <button
                    type="button"
                    class="tp-btn tp-btn-sm"
                    :class="showUnscheduled ? 'tp-btn-warning' : 'tp-btn-outline'"
                    @click="showUnscheduled = !showUnscheduled"
                >
                    <i class="fa fa-clock-o me-1"></i>
                    Chưa hẹn ngày ({{ unscheduledTasks.length }})
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="tp-filter-bar mb-3">
            <div class="tp-search-box">
                <i class="fa fa-search"></i>
                <input
                    type="text"
                    v-model="searchQuery"
                    class="tp-filter-input"
                    placeholder="Tìm theo tên công việc, key, nhãn..."
                >
                <button v-if="searchQuery" type="button" class="tp-clear-search-btn" @click="searchQuery = ''">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="tp-select-wrapper">
                <select v-model="selectedAssignee" class="tp-filter-select">
                    <option value="">👤 Người làm: Tất cả</option>
                    <option v-for="u in assigneesList" :key="u.id" :value="u.id">
                        👤 {{ u.name }}
                    </option>
                </select>
                <i class="fa fa-chevron-down tp-select-icon"></i>
            </div>

            <div class="tp-select-wrapper">
                <select v-model="selectedStatus" class="tp-filter-select">
                    <option value="">📌 Trạng thái: Tất cả</option>
                    <option v-for="st in workflowStatuses" :key="st.key" :value="st.key">
                        {{ st.name }}
                    </option>
                </select>
                <i class="fa fa-chevron-down tp-select-icon"></i>
            </div>

            <div class="tp-select-wrapper">
                <select v-model="selectedPriority" class="tp-filter-select">
                    <option value="">🔥 Ưu tiên: Tất cả</option>
                    <option value="high">Cao / Gấp</option>
                    <option value="medium">Trung bình</option>
                    <option value="low">Thấp</option>
                </select>
                <i class="fa fa-chevron-down tp-select-icon"></i>
            </div>

            <button v-if="hasActiveFilter" class="tp-btn tp-btn-sm tp-btn-danger-outline" @click="resetFilters">
                <i class="fa fa-times me-1"></i> Xóa lọc
            </button>
        </div>

        <!-- Toast feedback -->
        <div v-if="toastMessage" class="tp-toast-feedback mb-3">
            <i class="fa fa-check-circle me-2 font-size-15"></i>
            <span>{{ toastMessage }}</span>
            <button type="button" class="tp-toast-close" @click="toastMessage = ''">&times;</button>
        </div>

        <!-- MAIN CALENDAR CONTAINER (WITH OPTIONAL UNSCHEDULED DRAWER) -->
        <div class="tp-calendar-layout" :class="{ 'with-drawer': showUnscheduled }">
            <!-- 1. MONTH VIEW -->
            <div v-if="viewMode === 'month'" class="tp-cal-month-wrap">
                <!-- Day Names Header -->
                <div class="tp-cal-header-row">
                    <div class="tp-cal-header-cell">Thứ 2</div>
                    <div class="tp-cal-header-cell">Thứ 3</div>
                    <div class="tp-cal-header-cell">Thứ 4</div>
                    <div class="tp-cal-header-cell">Thứ 5</div>
                    <div class="tp-cal-header-cell">Thứ 6</div>
                    <div class="tp-cal-header-cell">Thứ 7</div>
                    <div class="tp-cal-header-cell is-weekend">Chủ nhật</div>
                </div>

                <!-- Month Days Grid -->
                <div class="tp-cal-days-grid">
                    <div
                        v-for="day in monthDays"
                        :key="day.dateStr"
                        class="tp-cal-day-cell"
                        :class="{
                            'is-other-month': !day.isCurrentMonth,
                            'is-today': day.isToday,
                            'has-tasks': day.tasks.length > 0,
                            'is-weekend': day.isWeekend
                        }"
                    >
                        <div class="tp-day-cell-top">
                            <span class="tp-day-num" :class="{ 'today-circle': day.isToday }">{{ day.dayNumber }}</span>
                            <span v-if="day.tasks.length > 0" class="tp-day-task-count">
                                {{ day.tasks.length }}
                            </span>
                            <a
                                :href="`/tasks/create?project_id=${project.id}&due_date=${day.dateStr}`"
                                class="tp-day-add-btn"
                                title="Thêm công việc cho ngày này"
                            >
                                <i class="fa fa-plus"></i>
                            </a>
                        </div>

                        <!-- Tasks Chips for this Day -->
                        <div class="tp-day-tasks-list">
                            <div
                                v-for="task in day.tasks.slice(0, 4)"
                                :key="task.id"
                                class="tp-cal-task-chip"
                                :style="{ borderLeftColor: getStatusColor(task.status) }"
                                @click="openTaskModal(task)"
                                :title="`${task.issue_key || 'TASK'}: ${task.name}`"
                            >
                                <span class="tp-chip-status-dot" :style="{ backgroundColor: getStatusColor(task.status) }"></span>
                                <strong class="tp-chip-key">{{ task.issue_key || 'TASK' }}</strong>
                                <span class="tp-chip-name">{{ task.name }}</span>
                                <span v-if="task.priority === 'high'" class="tp-chip-priority-dot" title="Ưu tiên cao"></span>
                                <i v-if="isTaskDone(task)" class="fa fa-check text-success ms-auto font-size-10"></i>
                            </div>

                            <!-- More tasks indicator -->
                            <div v-if="day.tasks.length > 4" class="tp-cal-more-chip" @click="viewDayAgenda(day)">
                                +{{ day.tasks.length - 4 }} công việc khác
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. WEEK VIEW -->
            <div v-else-if="viewMode === 'week'" class="tp-cal-week-wrap">
                <div class="tp-cal-week-grid">
                    <div
                        v-for="day in weekDays"
                        :key="day.dateStr"
                        class="tp-week-column"
                        :class="{ 'is-today': day.isToday }"
                    >
                        <div class="tp-week-col-header" :class="{ 'is-today': day.isToday }">
                            <div class="d-flex align-items-center gap-2">
                                <span class="tp-week-day-name">{{ day.weekdayName }}</span>
                                <span class="tp-week-day-num" :class="{ 'today-circle': day.isToday }">{{ day.dayNumber }}/{{ day.monthNumber }}</span>
                            </div>
                            <span class="tp-badge tp-badge-muted ms-auto">{{ day.tasks.length }} việc</span>
                        </div>

                        <div class="tp-week-col-body">
                            <div
                                v-for="task in day.tasks"
                                :key="task.id"
                                class="tp-week-task-card"
                                :style="{ borderLeftColor: getStatusColor(task.status) }"
                                @click="openTaskModal(task)"
                            >
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="tp-badge tp-badge-key">
                                        {{ task.issue_key || 'TASK-' + task.id }}
                                    </span>
                                    <span class="tp-badge" :style="{ backgroundColor: getStatusColor(task.status), color: '#fff', fontSize: '9px' }">
                                        {{ getStatusLabel(task.status) }}
                                    </span>
                                </div>
                                <h4 class="tp-week-task-title">{{ task.name }}</h4>
                                <div class="tp-week-card-footer">
                                    <span class="small text-muted">
                                        <i class="fa fa-user-circle-o me-1"></i>
                                        {{ task.assignee ? (task.assignee.name || task.assignee.full_name) : 'Chưa gán' }}
                                    </span>
                                    <span v-if="task.story_points" class="tp-badge tp-badge-points">
                                        {{ task.story_points }}pts
                                    </span>
                                </div>
                            </div>

                            <div v-if="day.tasks.length === 0" class="tp-week-empty">
                                <i class="fa fa-calendar-minus-o text-muted font-size-18 mb-1"></i>
                                <span class="text-muted small">Không có việc</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. AGENDA / LIST VIEW -->
            <div v-else class="tp-cal-agenda-wrap">
                <div v-if="agendaGroupedTasks.length > 0" class="tp-agenda-list">
                    <div v-for="group in agendaGroupedTasks" :key="group.dateStr" class="tp-agenda-group mb-3">
                        <div class="tp-agenda-date-header">
                            <i class="fa fa-calendar-check-o text-primary me-2"></i>
                            <strong>{{ group.formattedDate }}</strong>
                            <span class="text-muted ms-2">({{ group.tasks.length }} công việc)</span>
                        </div>
                        <div class="tp-agenda-cards">
                            <div
                                v-for="task in group.tasks"
                                :key="task.id"
                                class="tp-agenda-task-row"
                                @click="openTaskModal(task)"
                            >
                                <span class="status-indicator-bar" :style="{ backgroundColor: getStatusColor(task.status) }"></span>
                                <div class="tp-agenda-meta">
                                    <span class="tp-badge tp-badge-key">{{ task.issue_key || 'TASK' }}</span>
                                    <span class="tp-badge" :style="{ backgroundColor: getStatusColor(task.status), color: '#fff' }">
                                        {{ getStatusLabel(task.status) }}
                                    </span>
                                </div>
                                <div class="tp-agenda-title">
                                    <strong>{{ task.name }}</strong>
                                    <span v-if="task.description" class="tp-agenda-desc text-muted">
                                        {{ stripTags(task.description) }}
                                    </span>
                                </div>
                                <div class="tp-agenda-assignee text-muted small ms-auto">
                                    <i class="fa fa-user-circle-o me-1"></i>
                                    {{ task.assignee ? (task.assignee.name || task.assignee.full_name) : 'Chưa phân công' }}
                                </div>
                                <div class="tp-agenda-action">
                                    <button type="button" class="tp-btn tp-btn-sm tp-btn-outline" @click.stop="openTaskModal(task)">
                                        Xem chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="tp-empty-agenda">
                    <i class="fa fa-calendar-o font-size-36 text-muted mb-2"></i>
                    <p class="text-muted m-0">Không có công việc nào theo bộ lọc trong khoảng thời gian này.</p>
                </div>
            </div>

            <!-- UNSCHEDULED TASKS SIDE DRAWER -->
            <div v-if="showUnscheduled" class="tp-unscheduled-drawer">
                <div class="tp-drawer-header">
                    <h3 class="m-0 font-size-14 font-weight-bold">
                        <i class="fa fa-clock-o text-warning me-1"></i>
                        Chưa hẹn ngày ({{ unscheduledTasks.length }})
                    </h3>
                    <button type="button" class="tp-modal-close" @click="showUnscheduled = false">&times;</button>
                </div>
                <div class="tp-drawer-body">
                    <p class="small text-muted mb-3">Các công việc chưa có ngày kết thúc hoặc hạn hoàn thành. Chọn ngày để lên lịch:</p>
                    <div
                        v-for="task in unscheduledTasks"
                        :key="task.id"
                        class="tp-unscheduled-card"
                        @click="openTaskModal(task)"
                    >
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="tp-badge tp-badge-key">{{ task.issue_key || 'TASK' }}</span>
                            <span class="tp-badge" :style="{ backgroundColor: getStatusColor(task.status), color: '#fff', fontSize: '9px' }">
                                {{ getStatusLabel(task.status) }}
                            </span>
                        </div>
                        <strong class="font-size-13 d-block text-dark mb-2">{{ task.name }}</strong>
                        <div class="d-flex align-items-center gap-2">
                            <input
                                type="date"
                                class="tp-date-input-sm"
                                @click.stop
                                @change="quickScheduleTask(task, $event.target.value)"
                                title="Đặt ngày hoàn thành"
                            >
                        </div>
                    </div>
                    <div v-if="unscheduledTasks.length === 0" class="text-center p-3 text-muted small">
                        Tất cả công việc đã được lên lịch!
                    </div>
                </div>
            </div>
        </div>

        <!-- TASK DETAIL & WORKFLOW STATE MACHINE MODAL -->
        <div v-if="selectedTaskModal" class="tp-modal-overlay" @click.self="closeTaskModal">
            <div class="tp-modal-dialog">
                <div class="tp-modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="tp-badge tp-badge-key">{{ selectedTaskModal.issue_key || 'TASK' }}</span>
                        <h3 class="m-0 font-size-16 font-weight-bold">{{ selectedTaskModal.name }}</h3>
                    </div>
                    <button type="button" class="tp-modal-close" @click="closeTaskModal">&times;</button>
                </div>

                <div class="tp-modal-body">
                    <!-- Workflow State Machine Transition Pipeline -->
                    <div class="tp-workflow-progression-box mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small font-weight-bold text-uppercase text-muted">
                                <i class="fa fa-sliders text-primary me-1"></i>
                                Luồng quy trình (State Machine)
                            </span>
                            <span class="tp-badge" :style="{ backgroundColor: getStatusColor(selectedTaskModal.status), color: '#fff' }">
                                Hiện tại: {{ getStatusLabel(selectedTaskModal.status) }}
                            </span>
                        </div>

                        <!-- Progress Bar Nodes -->
                        <div class="tp-modal-pipeline-nodes">
                            <div
                                v-for="(st, idx) in workflowStatuses"
                                :key="st.key"
                                class="tp-modal-step"
                                :class="{
                                    'is-active': selectedTaskModal.status === st.key,
                                    'is-past': isStepPast(selectedTaskModal.status, st.key)
                                }"
                                @click="attemptTransition(st.key)"
                                :title="`Chuyển sang: ${st.name}`"
                            >
                                <div class="step-circle" :style="{ borderColor: st.color }">
                                    <i v-if="isStepPast(selectedTaskModal.status, st.key)" class="fa fa-check text-success"></i>
                                    <span v-else>{{ idx + 1 }}</span>
                                </div>
                                <span class="step-label">{{ st.name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Task Core Details -->
                    <div class="tp-task-detail-grid mb-3">
                        <div class="tp-detail-col">
                            <div class="small text-muted">Người thực hiện:</div>
                            <div class="font-weight-600 mt-1">
                                <i class="fa fa-user-circle-o text-primary me-1"></i>
                                {{ selectedTaskModal.assignee ? (selectedTaskModal.assignee.name || selectedTaskModal.assignee.full_name) : 'Chưa phân công' }}
                            </div>
                        </div>
                        <div class="tp-detail-col">
                            <div class="small text-muted">Hạn hoàn thành (Due date):</div>
                            <div class="font-weight-600 mt-1">
                                <i class="fa fa-calendar text-danger me-1"></i>
                                {{ formatDisplayDate(selectedTaskModal.due_date || selectedTaskModal.end_date || selectedTaskModal.start_date) || 'Chưa đặt hạn' }}
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedTaskModal.description" class="tp-modal-desc-box mb-3">
                        <div class="small text-muted mb-1 font-weight-bold">Mô tả công việc:</div>
                        <div class="small tp-desc-content" v-html="selectedTaskModal.description"></div>
                    </div>
                </div>

                <div class="tp-modal-footer">
                    <a :href="`/tasks/${selectedTaskModal.id}`" class="tp-btn tp-btn-sm tp-btn-outline">
                        <i class="fa fa-external-link me-1"></i> Xem trang chi tiết đầy đủ
                    </a>
                    <button type="button" class="tp-btn tp-btn-sm tp-btn-outline" @click="closeTaskModal">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    project: { type: Object, required: true },
    effectiveWorkflow: { type: Object, default: () => ({}) },
    tasks: { type: Array, default: () => [] },
    sprints: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});

// Reactive Task List
const tasksList = ref([...(props.project.tasks || props.tasks || [])]);

// Workflow Configuration
const workflow = computed(() => {
    return props.effectiveWorkflow || props.project.workflow_config || {
        project_type: 'software',
        statuses: [
            { key: 'open', name: 'Open', color: '#64748b' },
            { key: 'in_development', name: 'In Development', color: '#3b82f6' },
            { key: 'code_review', name: 'Code Review', color: '#8b5cf6' },
            { key: 'qa_testing', name: 'QA Testing', color: '#f59e0b' },
            { key: 'uat', name: 'UAT', color: '#06b6d4' },
            { key: 'done', name: 'Done', color: '#10b981', is_final: true },
        ],
    };
});

const workflowStatuses = computed(() => {
    return workflow.value.statuses || [
        { key: 'todo', name: 'Todo', color: '#64748b' },
        { key: 'in_progress', name: 'In Progress', color: '#3b82f6' },
        { key: 'done', name: 'Done', color: '#10b981', is_final: true },
    ];
});

// Calendar State
const currentDate = ref(new Date());
const viewMode = ref('month'); // 'month', 'week', 'agenda'
const showUnscheduled = ref(false);
const toastMessage = ref('');

// Month selector helpers
const monthNames = [
    'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4',
    'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8',
    'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
];

const selectedMonthIndex = ref(currentDate.value.getMonth());
const selectedYear = ref(currentDate.value.getFullYear());

function onMonthSelectChange() {
    const d = new Date(currentDate.value);
    d.setMonth(selectedMonthIndex.value);
    currentDate.value = d;
}

function onYearSelectChange() {
    const d = new Date(currentDate.value);
    d.setFullYear(selectedYear.value);
    currentDate.value = d;
}

// Available years range calculated from tasks or reasonable default
const availableYears = computed(() => {
    const yearsSet = new Set();
    const curY = new Date().getFullYear();
    for (let y = curY - 15; y <= curY + 5; y++) {
        yearsSet.add(y);
    }
    tasksList.value.forEach(t => {
        const d = parseTaskDate(t);
        if (d) yearsSet.add(d.getFullYear());
    });
    return Array.from(yearsSet).sort((a, b) => a - b);
});

// Filters
const searchQuery = ref('');
const selectedAssignee = ref('');
const selectedStatus = ref('');
const selectedPriority = ref('');

const hasActiveFilter = computed(() => {
    return searchQuery.value !== '' || selectedAssignee.value !== '' || selectedStatus.value !== '' || selectedPriority.value !== '';
});

function resetFilters() {
    searchQuery.value = '';
    selectedAssignee.value = '';
    selectedStatus.value = '';
    selectedPriority.value = '';
}

// Assignee list for filter dropdown
const assigneesList = computed(() => {
    const map = new Map();
    tasksList.value.forEach(t => {
        if (t.assignee && t.assignee.id) {
            map.set(t.assignee.id, t.assignee.name || t.assignee.full_name);
        }
    });
    return Array.from(map.entries()).map(([id, name]) => ({ id, name }));
});

// Filtered tasks
const filteredTasks = computed(() => {
    return tasksList.value.filter(task => {
        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            const name = (task.name || '').toLowerCase();
            const key = (task.issue_key || '').toLowerCase();
            const labels = (task.labels || '').toLowerCase();
            if (!name.includes(q) && !key.includes(q) && !labels.includes(q)) return false;
        }

        if (selectedAssignee.value) {
            const assigneeId = String(task.assignee_id || task.assignee?.id || '');
            if (assigneeId !== String(selectedAssignee.value)) return false;
        }

        if (selectedStatus.value) {
            if (task.status !== selectedStatus.value) return false;
        }

        if (selectedPriority.value) {
            if (task.priority !== selectedPriority.value) return false;
        }

        return true;
    });
});

// Tasks that have no date assigned
const unscheduledTasks = computed(() => {
    return filteredTasks.value.filter(t => !t.due_date && !t.end_date && !t.start_date);
});

const tasksWithDatesCount = computed(() => {
    return filteredTasks.value.filter(t => parseTaskDate(t) !== null).length;
});

// KPI Metrics
const monthTasksCount = computed(() => {
    const curYear = currentDate.value.getFullYear();
    const curMonth = currentDate.value.getMonth();
    return filteredTasks.value.filter(t => {
        const d = parseTaskDate(t);
        return d && d.getFullYear() === curYear && d.getMonth() === curMonth;
    }).length;
});

const completedTasksCount = computed(() => {
    return filteredTasks.value.filter(t => isTaskDone(t)).length;
});

const overdueTasksCount = computed(() => {
    const now = new Date();
    return filteredTasks.value.filter(t => {
        if (isTaskDone(t)) return false;
        const d = parseTaskDate(t);
        return d && d < now;
    }).length;
});

// Find nearest task month if current viewed month has 0 tasks
const nearestTaskMonth = computed(() => {
    if (monthTasksCount.value > 0) return null;
    const datedTasks = filteredTasks.value.map(t => parseTaskDate(t)).filter(Boolean);
    if (!datedTasks.length) return null;

    const curTime = currentDate.value.getTime();
    let closest = datedTasks[0];
    let minDiff = Math.abs(closest.getTime() - curTime);

    for (let i = 1; i < datedTasks.length; i++) {
        const diff = Math.abs(datedTasks[i].getTime() - curTime);
        if (diff < minDiff) {
            minDiff = diff;
            closest = datedTasks[i];
        }
    }
    return closest;
});

function jumpToNearestTaskMonth() {
    if (nearestTaskMonth.value) {
        currentDate.value = new Date(nearestTaskMonth.value);
        selectedMonthIndex.value = currentDate.value.getMonth();
        selectedYear.value = currentDate.value.getFullYear();
    }
}

// Period Labels
const currentPeriodLabel = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth() + 1;
    if (viewMode.value === 'month') {
        return `Tháng ${month} năm ${year}`;
    } else if (viewMode.value === 'week') {
        return `Tuần của Tháng ${month} / ${year}`;
    }
    return `Lịch trình tiến độ ${year}`;
});

const monthTitle = computed(() => `Tháng ${currentDate.value.getMonth() + 1}/${currentDate.value.getFullYear()}`);

// Calendar Period Navigation
function prevPeriod() {
    const d = new Date(currentDate.value);
    if (viewMode.value === 'month') {
        d.setMonth(d.getMonth() - 1);
    } else if (viewMode.value === 'week') {
        d.setDate(d.getDate() - 7);
    } else {
        d.setMonth(d.getMonth() - 1);
    }
    currentDate.value = d;
    selectedMonthIndex.value = d.getMonth();
    selectedYear.value = d.getFullYear();
}

function nextPeriod() {
    const d = new Date(currentDate.value);
    if (viewMode.value === 'month') {
        d.setMonth(d.getMonth() + 1);
    } else if (viewMode.value === 'week') {
        d.setDate(d.getDate() + 7);
    } else {
        d.setMonth(d.getMonth() + 1);
    }
    currentDate.value = d;
    selectedMonthIndex.value = d.getMonth();
    selectedYear.value = d.getFullYear();
}

function goToToday() {
    currentDate.value = new Date();
    selectedMonthIndex.value = currentDate.value.getMonth();
    selectedYear.value = currentDate.value.getFullYear();
}

// Generate Month Days Grid (6 weeks * 7 days)
const monthDays = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    // Monday as first day of week (0: Mon, 1: Tue ... 6: Sun)
    let startDayOfWeek = firstDay.getDay() - 1;
    if (startDayOfWeek === -1) startDayOfWeek = 6;

    const days = [];
    const todayStr = toDateString(new Date());

    // Fill days of previous month
    for (let i = startDayOfWeek - 1; i >= 0; i--) {
        const d = new Date(year, month, -i);
        const dateStr = toDateString(d);
        days.push({
            date: d,
            dateStr,
            dayNumber: d.getDate(),
            isCurrentMonth: false,
            isToday: dateStr === todayStr,
            isWeekend: d.getDay() === 0 || d.getDay() === 6,
            tasks: getTasksForDate(dateStr),
        });
    }

    // Fill days of current month
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const d = new Date(year, month, i);
        const dateStr = toDateString(d);
        days.push({
            date: d,
            dateStr,
            dayNumber: i,
            isCurrentMonth: true,
            isToday: dateStr === todayStr,
            isWeekend: d.getDay() === 0 || d.getDay() === 6,
            tasks: getTasksForDate(dateStr),
        });
    }

    // Fill remaining days of next month to complete 35 or 42 cells
    const remaining = 42 - days.length;
    for (let i = 1; i <= remaining; i++) {
        const d = new Date(year, month + 1, i);
        const dateStr = toDateString(d);
        days.push({
            date: d,
            dateStr,
            dayNumber: d.getDate(),
            isCurrentMonth: false,
            isToday: dateStr === todayStr,
            isWeekend: d.getDay() === 0 || d.getDay() === 6,
            tasks: getTasksForDate(dateStr),
        });
    }

    return days;
});

// Generate Week Days Grid
const weekDays = computed(() => {
    const curr = new Date(currentDate.value);
    let dayOfWeek = curr.getDay() - 1;
    if (dayOfWeek === -1) dayOfWeek = 6;

    const monday = new Date(curr);
    monday.setDate(curr.getDate() - dayOfWeek);

    const weekdays = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
    const days = [];
    const todayStr = toDateString(new Date());

    for (let i = 0; i < 7; i++) {
        const d = new Date(monday);
        d.setDate(monday.getDate() + i);
        const dateStr = toDateString(d);
        days.push({
            date: d,
            dateStr,
            dayNumber: d.getDate(),
            monthNumber: d.getMonth() + 1,
            weekdayName: weekdays[i],
            isToday: dateStr === todayStr,
            tasks: getTasksForDate(dateStr),
        });
    }

    return days;
});

// Agenda Grouped Tasks
const agendaGroupedTasks = computed(() => {
    const groups = new Map();
    filteredTasks.value.forEach(task => {
        const d = parseTaskDate(task);
        if (d) {
            const dateStr = toDateString(d);
            if (!groups.has(dateStr)) {
                groups.set(dateStr, []);
            }
            groups.get(dateStr).push(task);
        }
    });

    const sortedDates = Array.from(groups.keys()).sort();
    return sortedDates.map(dateStr => {
        const d = new Date(dateStr);
        return {
            dateStr,
            formattedDate: d.toLocaleDateString('vi-VN', { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' }),
            tasks: groups.get(dateStr),
        };
    });
});

function getTasksForDate(dateStr) {
    return filteredTasks.value.filter(task => {
        const taskDate = parseTaskDate(task);
        if (!taskDate) return false;
        return toDateString(taskDate) === dateStr;
    });
}

function parseTaskDate(task) {
    const raw = task.due_date || task.end_date || task.start_date;
    if (!raw) return null;
    return new Date(raw);
}

function toDateString(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function formatDisplayDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function stripTags(html) {
    if (!html) return '';
    return html.replace(/<[^>]*>/g, '').trim();
}

function getStatusColor(statusKey) {
    const st = workflowStatuses.value.find(s => s.key === statusKey);
    return st ? st.color : '#64748b';
}

function getStatusLabel(statusKey) {
    const st = workflowStatuses.value.find(s => s.key === statusKey);
    return st ? st.name : (statusKey || 'Cần làm');
}

function isTaskDone(task) {
    const s = (task.status || '').toLowerCase();
    return ['done', 'completed', 'resolved', 'closed'].includes(s);
}

function taskWorklogHours(task) {
    if (!task || !task.worklogs || !task.worklogs.length) return 0;
    return task.worklogs.reduce((acc, cur) => acc + (parseFloat(cur.hours) || 0), 0);
}

function isStepPast(currentKey, targetKey) {
    const statuses = workflowStatuses.value;
    const curIdx = statuses.findIndex(s => s.key === currentKey);
    const targetIdx = statuses.findIndex(s => s.key === targetKey);
    return curIdx >= 0 && targetIdx >= 0 && targetIdx < curIdx;
}

// Modal State & Action Handlers
const selectedTaskModal = ref(null);

function openTaskModal(task) {
    selectedTaskModal.value = task;
    transitionGateForm.value.show = false;
}

function closeTaskModal() {
    selectedTaskModal.value = null;
    transitionGateForm.value.show = false;
}

function viewDayAgenda(day) {
    currentDate.value = new Date(day.date);
    selectedMonthIndex.value = currentDate.value.getMonth();
    selectedYear.value = currentDate.value.getFullYear();
    viewMode.value = 'agenda';
}

// Quick Date Scheduling
async function quickScheduleTask(task, newDate) {
    if (!newDate) return;
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(`/tasks/${task.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                _method: 'PUT',
                due_date: newDate,
            })
        });

        const data = await res.json();
        if (data.success) {
            task.due_date = newDate;
            toastMessage.value = `Đã lên lịch công việc "${task.name}" vào ngày ${newDate}.`;
        }
    } catch (e) {
        console.error(e);
    }
}

async function attemptTransition(targetStatusKey) {
    if (!selectedTaskModal.value) return;
    const task = selectedTaskModal.value;
    if (task.status === targetStatusKey) return;

    // Direct move without gate blocking
    await executeTaskMove(task, targetStatusKey, {});
}

async function executeTaskMove(task, newStatus, extraPayload) {
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(`/tasks/${task.id}/move`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus,
                ...extraPayload
            })
        });

        const data = await res.json();
        if (data.success) {
            task.status = newStatus;
            if (data.task) {
                Object.assign(task, data.task);
            }
            toastMessage.value = `Công việc "${task.name}" đã chuyển sang trạng thái "${getStatusLabel(newStatus)}".`;
        } else {
            alert(data.message || 'Không thể chuyển trạng thái công việc.');
        }
    } catch (e) {
        console.error(e);
        alert('Lỗi kết nối khi cập nhật trạng thái.');
    }
}
</script>

<style scoped>
/* Main Container */
.project-calendar-vue {
    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #1e293b;
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 24px;
    box-sizing: border-box;
}

/* Flex & Layout Utilities (Independent of Bootstrap) */
.d-flex { display: flex; }
.flex-wrap { flex-wrap: wrap; }
.align-items-center { align-items: center; }
.justify-content-between { justify-content: space-between; }
.justify-content-end { justify-content: flex-end; }
.flex-grow-1 { flex-grow: 1; }
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 16px; }
.ms-auto { margin-left: auto; }
.me-1 { margin-right: 4px; }
.me-2 { margin-right: 8px; }
.ms-1 { margin-left: 4px; }
.mb-1 { margin-bottom: 4px; }
.mb-2 { margin-bottom: 8px; }
.mb-3 { margin-bottom: 16px; }
.mb-4 { margin-bottom: 24px; }
.m-0 { margin: 0; }
.small { font-size: 12px; }
.text-muted { color: #64748b !important; }
.text-dark { color: #0f172a !important; }
.text-primary { color: #2563eb !important; }
.text-success { color: #16a34a !important; }
.text-warning { color: #d97706 !important; }
.text-danger { color: #dc2626 !important; }
.font-weight-600 { font-weight: 600; }
.font-weight-bold { font-weight: 700; }
.font-size-10 { font-size: 10px; }
.font-size-13 { font-size: 13px; }
.font-size-14 { font-size: 14px; }
.font-size-15 { font-size: 15px; }
.font-size-16 { font-size: 16px; }
.font-size-18 { font-size: 18px; }
.font-size-36 { font-size: 36px; }

/* Custom Modern Buttons */
.tp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid transparent;
    text-decoration: none;
    line-height: 1.4;
    white-space: nowrap;
}
.tp-btn:hover {
    text-decoration: none;
}
.tp-btn-primary {
    background: #2563eb;
    color: #ffffff !important;
    border-color: #2563eb;
}
.tp-btn-primary:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
}
.tp-btn-outline {
    background: #ffffff;
    color: #475569 !important;
    border-color: #cbd5e1;
}
.tp-btn-outline:hover {
    background: #f8fafc;
    color: #1e293b !important;
    border-color: #94a3b8;
}
.tp-btn-warning {
    background: #fef3c7;
    color: #92400e !important;
    border-color: #fde68a;
}
.tp-btn-warning:hover {
    background: #fde68a;
}
.tp-btn-success {
    background: #16a34a;
    color: #ffffff !important;
    border-color: #16a34a;
}
.tp-btn-success:hover {
    background: #15803d;
}
.tp-btn-danger-outline {
    background: #fff;
    color: #dc2626 !important;
    border-color: #fca5a5;
}
.tp-btn-danger-outline:hover {
    background: #fef2f2;
    border-color: #f87171;
}
.tp-btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 6px;
}
.tp-btn-group {
    display: inline-flex;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.tp-btn-group .tp-btn {
    border-radius: 0;
    margin: 0;
    border-right-width: 0;
}
.tp-btn-group .tp-btn:first-child {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}
.tp-btn-group .tp-btn:last-child {
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
    border-right-width: 1px;
}

/* Custom Badges */
.tp-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.2;
}
.tp-badge-workflow {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.tp-badge-muted {
    background: #f1f5f9;
    color: #475569;
}
.tp-badge-key {
    background: #f1f5f9;
    color: #334155;
    font-family: monospace;
    font-size: 10px;
    font-weight: 700;
}
.tp-badge-points {
    background: #f3e8ff;
    color: #7e22ce;
    font-size: 9px;
}
.tp-badge-success-subtle {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}
.tp-badge-warning-subtle {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}
.tp-badge-danger-subtle {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

/* Header & Breadcrumb */
.pd-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}
.pd-breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
}
.pd-breadcrumb a {
    color: #64748b;
    text-decoration: none;
}
.pd-breadcrumb a:hover {
    color: #2563eb;
}
.pd-breadcrumb-active {
    color: #0f172a;
    font-weight: 700;
}
.pd-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.pd-title {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
}
.pd-project-key {
    padding: 3px 8px;
    background: #f1f5f9;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
    border-radius: 6px;
    font-family: monospace;
}
.pd-project-status {
    padding: 3px 8px;
    background: #ecfdf5;
    color: #059669;
    font-size: 11px;
    font-weight: 700;
    border-radius: 6px;
    border: 1px solid #a7f3d0;
}
.pd-settings-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    text-decoration: none;
}
.pd-settings-btn:hover {
    color: #0f172a;
    background: #edf2f7;
}

/* Project Sub Nav */
.pd-project-nav {
    display: flex;
    align-items: center;
    gap: 4px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 2px;
    overflow-x: auto;
}
.pd-nav-item {
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.15s;
    white-space: nowrap;
}
.pd-nav-item:hover {
    color: #2563eb;
    text-decoration: none;
}
.pd-nav-item.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
}

/* Stats Cards */
.tp-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}
@media (max-width: 900px) {
    .tp-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 500px) {
    .tp-stats-grid { grid-template-columns: 1fr; }
}
.tp-stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    transition: transform 0.15s, box-shadow 0.15s;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.tp-stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.tp-stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.tp-stat-header i {
    float: none !important;
    font-size: 16px;
    line-height: 1;
}
.tp-stat-label {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.tp-stat-value {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
    margin: 4px 0;
    display: block;
}
.tp-stat-hint {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 4px;
    display: block;
}

/* Smart Jump Banner */
.tp-cal-jump-banner {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

/* Calendar Control Bar */
.tp-cal-control-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    flex-wrap: wrap;
    gap: 12px;
}
.tp-cal-nav-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.tp-date-jump-selectors {
    display: flex;
    align-items: center;
    gap: 6px;
}
.tp-select {
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 5px 8px;
    font-size: 12px;
    font-weight: 600;
    background: #fff;
    color: #334155;
    outline: none;
    cursor: pointer;
}
.tp-select:focus {
    border-color: #2563eb;
}
.tp-cal-month-title {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
}
.tp-cal-mode-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Filter Bar */
.tp-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.tp-search-box {
    position: relative;
    flex-grow: 1;
    min-width: 220px;
}
.tp-search-box i.fa-search {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 13px;
}
.tp-filter-input {
    width: 100%;
    height: 36px;
    padding: 6px 32px 6px 34px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    background: #fff;
    color: #1e293b;
    outline: none;
    box-sizing: border-box;
}
.tp-filter-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
}
.tp-clear-search-btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
}
.tp-select-wrapper {
    position: relative;
    min-width: 160px;
}
.tp-filter-select {
    width: 100%;
    height: 36px;
    padding: 6px 28px 6px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    background: #fff;
    color: #1e293b;
    outline: none;
    appearance: none;
    cursor: pointer;
    box-sizing: border-box;
}
.tp-filter-select:focus {
    border-color: #2563eb;
}
.tp-select-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 11px;
    pointer-events: none;
}

/* Toast Feedback */
.tp-toast-feedback {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    padding: 10px 14px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    font-size: 13px;
    font-weight: 500;
}
.tp-toast-close {
    margin-left: auto;
    background: transparent;
    border: none;
    font-size: 18px;
    color: #059669;
    cursor: pointer;
    line-height: 1;
}

/* Layout with side drawer */
.tp-calendar-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    transition: all 0.25s ease;
}
.tp-calendar-layout.with-drawer {
    grid-template-columns: 1fr 340px;
}
@media (max-width: 1024px) {
    .tp-calendar-layout.with-drawer { grid-template-columns: 1fr; }
}

/* 1. MONTH VIEW */
.tp-cal-month-wrap {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.tp-cal-header-row {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.tp-cal-header-cell {
    padding: 12px 8px;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.tp-cal-header-cell.is-weekend {
    color: #dc2626;
}
.tp-cal-days-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}
.tp-cal-day-cell {
    min-height: 125px;
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    padding: 6px;
    background: #fff;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: background 0.15s;
    box-sizing: border-box;
}
.tp-cal-day-cell:nth-child(7n) { border-right: none; }
.tp-cal-day-cell:hover {
    background: #fcfdfe;
}
.tp-cal-day-cell.is-other-month {
    background: #fafbfc;
    opacity: 0.55;
}
.tp-cal-day-cell.is-today {
    background: #eff6ff;
}
.tp-day-cell-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}
.tp-day-num {
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.today-circle {
    background: #2563eb;
    color: #fff !important;
    font-weight: 700;
}
.tp-day-task-count {
    font-size: 10px;
    font-weight: 700;
    background: #f1f5f9;
    color: #64748b;
    padding: 1px 6px;
    border-radius: 10px;
}
.tp-day-add-btn {
    opacity: 0;
    color: #2563eb;
    font-size: 11px;
    padding: 2px 4px;
    border-radius: 4px;
    transition: opacity 0.15s;
    text-decoration: none;
}
.tp-cal-day-cell:hover .tp-day-add-btn {
    opacity: 1;
}

/* Day Task Chips */
.tp-day-tasks-list {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
}
.tp-cal-task-chip {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left-width: 3px;
    border-radius: 5px;
    padding: 3px 6px;
    font-size: 11px;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    transition: transform 0.15s, box-shadow 0.15s;
}
.tp-cal-task-chip:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
}
.tp-chip-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}
.tp-chip-key {
    font-size: 9.5px;
    color: #475569;
    font-family: monospace;
    flex-shrink: 0;
}
.tp-chip-name {
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 500;
    font-size: 11px;
}
.tp-chip-priority-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #ef4444;
    flex-shrink: 0;
}
.tp-cal-more-chip {
    font-size: 10.5px;
    color: #2563eb;
    font-weight: 700;
    padding: 2px 4px;
    cursor: pointer;
}

/* 2. WEEK VIEW */
.tp-cal-week-wrap {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}
.tp-cal-week-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    min-height: 500px;
}
@media (max-width: 900px) {
    .tp-cal-week-grid { grid-template-columns: 1fr; }
}
.tp-week-column {
    border-right: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
}
.tp-week-column:last-child { border-right: none; }
.tp-week-col-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.tp-week-col-header.is-today {
    background: #eff6ff;
}
.tp-week-day-name {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
}
.tp-week-day-num {
    font-size: 12px;
    font-weight: 600;
    color: #0f172a;
}
.tp-week-col-body {
    padding: 8px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #fafbfc;
}
.tp-week-task-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-left-width: 4px;
    border-radius: 6px;
    padding: 10px;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.tp-week-task-card:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}
.tp-week-task-title {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    margin: 4px 0 8px 0;
    line-height: 1.35;
}
.tp-week-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #f1f5f9;
    padding-top: 6px;
}
.tp-week-empty {
    padding: 24px 8px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* 3. AGENDA VIEW */
.tp-cal-agenda-wrap {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
}
.tp-agenda-date-header {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    padding-bottom: 6px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 8px;
}
.tp-agenda-cards {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.tp-agenda-task-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: background 0.15s;
}
.tp-agenda-task-row:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
}
.status-indicator-bar {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
}
.tp-agenda-meta {
    display: flex;
    align-items: center;
    gap: 6px;
}
.tp-agenda-title {
    flex: 1;
    min-width: 0;
}
.tp-agenda-title strong {
    font-size: 13.5px;
    color: #0f172a;
}
.tp-agenda-desc {
    display: block;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 500px;
}
.tp-empty-agenda {
    text-align: center;
    padding: 40px 16px;
}

/* Unscheduled Drawer */
.tp-unscheduled-drawer {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 700px;
}
.tp-drawer-header {
    padding: 14px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.tp-drawer-body {
    padding: 14px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.tp-unscheduled-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.15s;
}
.tp-unscheduled-card:hover {
    border-color: #3b82f6;
    background: #fff;
}
.tp-date-input-sm {
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 3px 6px;
    font-size: 11px;
    color: #334155;
    background: #fff;
}

/* Task Detail Modal */
.tp-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 16px;
}
.tp-modal-dialog {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 680px;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25);
    overflow: hidden;
    animation: modalPop 0.2s ease-out;
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.tp-modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}
.tp-modal-close {
    background: transparent;
    border: none;
    font-size: 22px;
    color: #64748b;
    cursor: pointer;
    line-height: 1;
    padding: 0 4px;
}
.tp-modal-close:hover {
    color: #0f172a;
}
.tp-modal-body {
    padding: 20px;
    max-height: 75vh;
    overflow-y: auto;
}
.tp-modal-footer {
    padding: 14px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Pipeline Progression */
.tp-modal-pipeline-nodes {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    margin-top: 12px;
}
.tp-modal-pipeline-nodes::before {
    content: '';
    position: absolute;
    top: 14px;
    left: 20px;
    right: 20px;
    height: 2px;
    background: #e2e8f0;
    z-index: 1;
}
.tp-modal-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    position: relative;
    z-index: 2;
    cursor: pointer;
}
.step-circle {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    transition: all 0.2s;
}
.tp-modal-step.is-active .step-circle {
    background: #2563eb;
    border-color: #2563eb !important;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.2);
}
.tp-modal-step.is-past .step-circle {
    background: #f0fdf4;
    border-color: #16a34a !important;
    color: #16a34a;
}
.step-label {
    font-size: 10px;
    font-weight: 600;
    color: #475569;
    max-width: 60px;
    text-align: center;
    line-height: 1.2;
}

.tp-task-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.tp-detail-col {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
}
.tp-modal-desc-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
}
.tp-gate-status-panel {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
}
.tp-gate-prompt-box {
    background: #eff6ff;
    border: 2px solid #3b82f6;
    border-radius: 8px;
    padding: 14px 16px;
}
.tp-form-input-sm {
    width: 100%;
    height: 34px;
    padding: 5px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    box-sizing: border-box;
}
.tp-input-addon-group {
    display: flex;
    align-items: center;
}
.tp-input-addon-group input {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
.tp-input-addon {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-left: none;
    border-top-right-radius: 6px;
    border-bottom-right-radius: 6px;
    padding: 7px 10px;
    font-size: 12px;
    color: #475569;
}

/* ================= DARK MODE SUPPORT ================= */
:global(.tp-theme-dark) .project-calendar-vue {
    color: #cbd5e1;
}
:global(.tp-theme-dark) .pd-title {
    color: #f8fafc;
}
:global(.tp-theme-dark) .pd-breadcrumb-active {
    color: #f1f5f9;
}
:global(.tp-theme-dark) .pd-settings-btn {
    background: #1e293b;
    border-color: #334155;
    color: #94a3b8;
}
:global(.tp-theme-dark) .tp-stat-card,
:global(.tp-theme-dark) .tp-cal-control-bar,
:global(.tp-theme-dark) .tp-cal-month-wrap,
:global(.tp-theme-dark) .tp-cal-week-wrap,
:global(.tp-theme-dark) .tp-cal-agenda-wrap,
:global(.tp-theme-dark) .tp-unscheduled-drawer,
:global(.tp-theme-dark) .tp-modal-dialog {
    background: #1e293b !important;
    border-color: #334155 !important;
}
:global(.tp-theme-dark) .tp-stat-value,
:global(.tp-theme-dark) .tp-cal-month-title,
:global(.tp-theme-dark) .tp-agenda-title strong {
    color: #f8fafc !important;
}
:global(.tp-theme-dark) .tp-cal-header-row,
:global(.tp-theme-dark) .tp-week-col-header,
:global(.tp-theme-dark) .tp-drawer-header,
:global(.tp-theme-dark) .tp-modal-header,
:global(.tp-theme-dark) .tp-modal-footer {
    background: #0f172a !important;
    border-color: #334155 !important;
}
:global(.tp-theme-dark) .tp-cal-day-cell {
    background: #1e293b !important;
    border-color: #334155 !important;
}
:global(.tp-theme-dark) .tp-cal-day-cell:hover {
    background: #27354a !important;
}
:global(.tp-theme-dark) .tp-cal-day-cell.is-other-month {
    background: #162032 !important;
}
:global(.tp-theme-dark) .tp-cal-day-cell.is-today {
    background: #1e3a8a33 !important;
}
:global(.tp-theme-dark) .tp-cal-task-chip,
:global(.tp-theme-dark) .tp-week-task-card {
    background: #0f172a !important;
    border-color: #334155 !important;
}
:global(.tp-theme-dark) .tp-chip-name,
:global(.tp-theme-dark) .tp-week-task-title {
    color: #f1f5f9 !important;
}
:global(.tp-theme-dark) .tp-day-num {
    color: #cbd5e1 !important;
}
:global(.tp-theme-dark) .tp-filter-input,
:global(.tp-theme-dark) .tp-filter-select,
:global(.tp-theme-dark) .tp-select {
    background: #0f172a !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
:global(.tp-theme-dark) .tp-btn-outline {
    background: #0f172a !important;
    border-color: #334155 !important;
    color: #cbd5e1 !important;
}
:global(.tp-theme-dark) .tp-btn-outline:hover {
    background: #1e293b !important;
    color: #fff !important;
}
:global(.tp-theme-dark) .tp-agenda-task-row,
:global(.tp-theme-dark) .tp-unscheduled-card,
:global(.tp-theme-dark) .tp-detail-col,
:global(.tp-theme-dark) .tp-modal-desc-box,
:global(.tp-theme-dark) .tp-gate-status-panel {
    background: #0f172a !important;
    border-color: #334155 !important;
}
:global(.tp-theme-dark) .tp-modal-overlay {
    background: rgba(0, 0, 0, 0.75);
}
:global(.tp-theme-dark) .step-circle {
    background: #0f172a;
    border-color: #475569;
    color: #94a3b8;
}
:global(.tp-theme-dark) .step-label {
    color: #94a3b8;
}
</style>
