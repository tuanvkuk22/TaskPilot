<template>
    <div class="task-timeline-vue project-dashboard-vue w-100">
        <!-- 1. STANDARD JIRA PROJECT HEADER -->
        <div class="pd-header mb-2">
            <div class="pd-header-left">
                <div class="pd-breadcrumb mb-1">
                    <a href="/projects">Dự án</a>
                    <span>/</span>
                    <span>{{ project.name }}</span>
                    <span>/</span>
                    <span class="text-dark font-weight-bold">Timeline</span>
                </div>
                <div class="pd-title-row">
                    <h1 class="pd-title">{{ project.name }}</h1>
                    <span class="pd-project-key">{{ project.project_code || 'PROJ' }}</span>
                    <span class="pd-project-status">{{ project.status || 'ACTIVE' }}</span>
                    <a :href="`/projects/${project.id}/edit`" class="pd-settings-btn" title="Cài đặt dự án">
                        <i class="fa fa-cog"></i>
                    </a>
                </div>
            </div>
            <!-- Create Buttons -->
            <div class="d-flex align-items-center gap-2">
                <button type="button" @click="showInlineEpicCreate = true" class="tp-btn tp-btn-secondary timeline-create-btn" style="background: #8b5cf6; border-color: #8b5cf6; height: 34px; padding: 0 14px; font-size: 13px;">
                    <i class="fa fa-bolt" style="color: #ffffff !important;"></i>
                    <span>+ Tạo Epic</span>
                </button>
                <a :href="`/tasks/create?project_id=${project.id}`" class="tp-btn tp-btn-primary timeline-create-btn" style="height: 34px; padding: 0 14px; font-size: 13px;">
                    <i class="fa fa-plus-circle" style="color: #ffffff !important;"></i>
                    <span>+ Tạo Issue</span>
                </a>
            </div>
        </div>

        <!-- 2. EXACT JIRA SUB-NAV TAB BAR -->
        <div class="pd-project-nav mb-3">
            <a href="/projects" class="pd-nav-item">Dự án</a>
            <a :href="`/projects/${project.id}`" class="pd-nav-item">Tổng quan</a>
            <a :href="`/projects/${project.id}/board`" class="pd-nav-item">Bảng (Board)</a>
            <a :href="`/projects/${project.id}/members`" class="pd-nav-item">Thành viên</a>
            <a :href="`/projects/${project.id}/roles`" class="pd-nav-item">Vai trò &amp; Quyền</a>
            <a :href="`/sprints?project_id=${project.id}`" class="pd-nav-item">Backlog / Sprint</a>
            <a :href="`/projects/${project.id}/timeline`" class="pd-nav-item active">Timeline</a>
            <a :href="`/projects/${project.id}/roadmap`" class="pd-nav-item">Roadmap</a>
        </div>

        <!-- 3. JIRA SEARCH & FILTER TOOLBAR -->
        <div class="tp-filter-bar d-flex flex-wrap gap-2 align-items-center mb-3">
            <div class="tp-filter-inputs d-flex flex-wrap gap-2 align-items-center flex-grow-1">
                <!-- Search Box -->
                <div class="tp-search-box" style="width: 220px;">
                    <i class="fa fa-search"></i>
                    <input
                        type="text"
                        v-model="searchQuery"
                        class="edit-input rounded-pill"
                        style="height: 34px; font-size: 13px;"
                        placeholder="Search timeline..."
                    />
                </div>

                <!-- Epic Select Filter -->
                <div class="edit-select-wrapper" style="min-width: 150px; height: 34px;">
                    <select v-model="filterEpic" class="edit-select" style="height: 34px; font-size: 13px;">
                        <option value="">⚡ Tất cả Epic</option>
                        <option v-for="epic in epicOptions" :key="epic.id" :value="epic.id">
                            ⚡ {{ epic.name }}
                        </option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Issue Type Select Filter -->
                <div class="edit-select-wrapper" style="min-width: 130px; height: 34px;">
                    <select v-model="filterType" class="edit-select" style="height: 34px; font-size: 13px;">
                        <option value="">Loại Issue: Tất cả</option>
                        <option value="epic">Epic</option>
                        <option value="task">Task</option>
                        <option value="bug">Bug</option>
                        <option value="story">Story</option>
                        <option value="subtask">Sub-task</option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Status Select Filter -->
                <div class="edit-select-wrapper" style="min-width: 130px; height: 34px;">
                    <select v-model="filterStatus" class="edit-select" style="height: 34px; font-size: 13px;">
                        <option value="">Trạng thái: Tất cả</option>
                        <option value="todo">Cần làm</option>
                        <option value="in_progress">Đang làm</option>
                        <option value="done">Hoàn thành</option>
                    </select>
                    <i class="fa fa-chevron-down edit-select-icon"></i>
                </div>

                <!-- Expand/Collapse Buttons -->
                <button @click="expandAll" class="tp-btn tp-btn-light" style="height: 34px; padding: 0 10px; font-size: 12px;" title="Mở rộng tất cả">
                    <i class="fa fa-expand"></i> Mở rộng
                </button>
                <button @click="collapseAll" class="tp-btn tp-btn-light" style="height: 34px; padding: 0 10px; font-size: 12px;" title="Thu gọn tất cả">
                    <i class="fa fa-compress"></i> Thu gọn
                </button>
            </div>

            <!-- Team Avatars Stack -->
            <div class="timeline-team-stack ms-auto" v-if="teamMembers.length">
                <div
                    v-for="(member, idx) in teamMembers.slice(0, 5)"
                    :key="member.id || idx"
                    class="team-avatar-item"
                    :title="member.name || member.full_name"
                >
                    {{ getInitials(member.name || member.full_name) }}
                </div>
                <div v-if="teamMembers.length > 5" class="team-avatar-item more">
                    +{{ teamMembers.length - 5 }}
                </div>
            </div>
        </div>

        <!-- 4. TIMELINE UNIFIED FULL-WIDTH WORKSPACE WITH GLOBAL HORIZONTAL SCROLL -->
        <div class="timeline-workspace-outer" ref="workspaceOuter">
            <div class="timeline-workspace-inner">
                <!-- Left Pane Table (Columns: Work | Status | Assignee) -->
                <div class="timeline-left-pane">
                    <div class="pane-header d-flex align-items-center">
                        <div style="width: 24px;" class="flex-shrink-0 text-center">
                            <input type="checkbox" class="form-check-input">
                        </div>
                        <div class="flex-grow-1 font-weight-bold text-truncate me-2" style="min-width: 0; padding-left: 6px;">
                            <i class="fa fa-chevron-right me-1 text-muted font-size-11"></i> Work
                        </div>
                        <div style="width: 100px;" class="flex-shrink-0 font-weight-bold text-center">Status</div>
                        <div style="width: 60px;" class="flex-shrink-0 font-weight-bold text-center">Assignee</div>
                    </div>

                    <div class="pane-body" ref="leftPaneBody" @scroll="syncVerticalScroll('left')">
                        <template v-if="visibleRows.length">
                            <div
                                v-for="task in visibleRows"
                                :key="task.id"
                                class="timeline-task-row"
                                :class="[
                                    `level-${task.level || 0}`,
                                    {
                                        'is-epic': task.issue_type === 'epic',
                                        'is-virtual': task.isVirtualGroup,
                                        'is-inline-row': task.isInlineTaskRow,
                                        'is-selected': selectedTaskForDetail?.id === task.id
                                    }
                                ]"
                                @click="!task.isInlineTaskRow && openDetailDrawer(task)"
                                @mouseenter="hoveredTaskId = task.id"
                                @mouseleave="hoveredTaskId = null"
                            >
                                <!-- Inline Creation Row under Epic -->
                                <template v-if="task.isInlineTaskRow">
                                    <div style="width: 24px;" class="flex-shrink-0"></div>
                                    <div class="d-flex align-items-center flex-grow-1 gap-2 me-2" style="min-width: 0;" :style="{ paddingLeft: (task.level * 14) + 'px' }" @click.stop>
                                        <i class="fa fa-plus-circle text-primary font-size-13 flex-shrink-0"></i>
                                        <input
                                            ref="inlineTaskInput"
                                            type="text"
                                            v-model="inlineTaskTitle"
                                            class="edit-input input-sm flex-grow-1"
                                            style="height: 28px; font-size: 12.5px; border-radius: 4px; min-width: 0;"
                                            placeholder="Nhập tên task mới và nhấn Enter..."
                                            @keyup.enter="handleCreateTaskForEpic(task.parentEpicId)"
                                            @keyup.esc="inlineCreateEpicId = null"
                                        >
                                        <button type="button" class="tp-btn tp-btn-primary btn-xs flex-shrink-0" style="height: 28px; padding: 0 10px; font-size: 11px;" @click="handleCreateTaskForEpic(task.parentEpicId)">Tạo Task</button>
                                        <button type="button" class="tp-btn tp-btn-light btn-xs flex-shrink-0" style="height: 28px; padding: 0 8px;" @click="inlineCreateEpicId = null"><i class="fa fa-times"></i></button>
                                    </div>
                                    <div style="width: 100px;" class="flex-shrink-0"></div>
                                    <div style="width: 60px;" class="flex-shrink-0"></div>
                                </template>

                                <!-- Standard Task / Epic Row -->
                                <template v-else>
                                    <!-- Checkbox column -->
                                    <div style="width: 24px;" class="flex-shrink-0 text-center" @click.stop>
                                        <input type="checkbox" class="form-check-input" v-if="!task.isVirtualGroup">
                                    </div>

                                    <!-- Work Column -->
                                    <div class="d-flex align-items-center flex-grow-1 text-truncate me-2" style="min-width: 0; overflow: hidden;" :style="{ paddingLeft: (task.level * 14) + 'px' }">
                                        <button
                                            v-if="hasChildren(task)"
                                            @click.stop="toggleCollapse(task.id)"
                                            class="collapse-btn flex-shrink-0"
                                        >
                                            <i :class="['fa', collapsedSet.has(String(task.id)) ? 'fa-chevron-right' : 'fa-chevron-down']"></i>
                                        </button>
                                        <span v-else class="tree-leaf-spacer flex-shrink-0"></span>

                                        <!-- Issue Type Icon -->
                                        <i v-if="task.isVirtualGroup" class="fa fa-folder-open text-muted me-1 flex-shrink-0"></i>
                                        <i v-else-if="task.issue_type === 'epic'" class="fa fa-bolt text-purple me-1 font-size-13 flex-shrink-0"></i>
                                        <i v-else-if="task.issue_type === 'story'" class="fa fa-bookmark text-success me-1 font-size-12 flex-shrink-0"></i>
                                        <i v-else-if="task.issue_type === 'bug'" class="fa fa-bug text-danger me-1 font-size-12 flex-shrink-0"></i>
                                        <i v-else-if="task.issue_type === 'subtask'" class="fa fa-level-up fa-rotate-90 text-info font-size-11 me-1 flex-shrink-0"></i>
                                        <i v-else class="fa fa-check-square-o text-primary me-1 font-size-12 flex-shrink-0"></i>

                                        <!-- Issue Key Badge -->
                                        <span v-if="!task.isVirtualGroup" class="task-key-blue me-1 flex-shrink-0">{{ getTaskKey(task) }}</span>

                                        <!-- Single Line Task Name -->
                                        <span
                                            class="task-name-text text-truncate flex-grow-1"
                                            style="min-width: 0;"
                                            :class="{ 'font-weight-bold': task.issue_type === 'epic' || task.isVirtualGroup }"
                                            :title="task.name"
                                        >
                                            {{ task.name }}
                                        </span>

                                        <!-- Plus Icon for Epic to Create Task -->
                                        <button
                                            v-if="task.issue_type === 'epic'"
                                            type="button"
                                            class="epic-quick-add-btn ms-1 flex-shrink-0"
                                            title="Tạo task mới thuộc Epic này"
                                            @click.stop="toggleInlineTaskForEpic(task.id)"
                                        >
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>

                                    <!-- Status Column -->
                                    <div style="width: 100px;" class="flex-shrink-0 text-center" @click.stop>
                                        <div v-if="task.issue_type === 'epic'" class="epic-status-segment-bar mx-auto">
                                            <div class="seg-in-progress" :style="{ width: getEpicSegmentPercent(task, 'in_progress') + '%' }"></div>
                                            <div class="seg-done" :style="{ width: getEpicSegmentPercent(task, 'done') + '%' }"></div>
                                        </div>
                                        <StatusBadge v-else-if="!task.isVirtualGroup" :status="task.status || 'todo'" />
                                    </div>

                                    <!-- Assignee Column: ONLY AVATAR, NO NAME. NO ASSIGNEE FOR EPIC -->
                                    <div style="width: 60px;" class="flex-shrink-0 d-flex justify-content-center align-items-center" @click.stop>
                                        <template v-if="!task.isVirtualGroup && task.issue_type !== 'epic'">
                                            <UserAvatarPill v-if="task.assignee" :name="task.assignee.name" :only-avatar="true" />
                                            <span v-else class="text-muted small" title="Chưa phân công" style="font-size: 11px;">-</span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <div v-else class="timeline-empty-left">
                            <i class="fa fa-inbox font-size-24 text-muted mb-2 d-block"></i>
                            <span>Không tìm thấy công việc nào trong timeline.</span>
                        </div>

                        <!-- Bottom Inline Create Epic Row -->
                        <div class="timeline-bottom-create-row p-2 border-top bg-light">
                            <div v-if="!showInlineEpicCreate" class="d-flex align-items-center gap-2 cursor-pointer text-primary" @click="showInlineEpicCreate = true">
                                <i class="fa fa-plus-circle font-size-14"></i>
                                <span class="font-size-13 font-weight-bold">+ Create Epic / Issue</span>
                            </div>
                            <div v-else class="d-flex align-items-center gap-2">
                                <span class="badge bg-purple text-white"><i class="fa fa-bolt me-1"></i> Epic</span>
                                <input
                                    type="text"
                                    v-model="newEpicInlineTitle"
                                    class="edit-input input-sm flex-grow-1"
                                    placeholder="Nhập tên Epic mới và nhấn Enter..."
                                    @keyup.enter="handleCreateInlineEpic"
                                    @keyup.esc="showInlineEpicCreate = false"
                                >
                                <button type="button" class="tp-btn tp-btn-primary btn-xs" @click="handleCreateInlineEpic">Tạo Epic</button>
                                <button type="button" class="tp-btn tp-btn-light btn-xs" @click="showInlineEpicCreate = false"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Pane: Gantt Chart Grid -->
                <div class="timeline-right-pane">
                    <!-- Two-Tier Date Header -->
                    <div class="gantt-header-container" ref="ganttHeaderRef">
                        <div class="gantt-header-top-row">
                            <div
                                v-for="group in dateHeaderGroups"
                                :key="group.label"
                                class="gantt-header-top-cell"
                                :style="{ flex: group.span }"
                            >
                                {{ group.label }}
                            </div>
                        </div>
                        <div class="gantt-header-bottom-row">
                            <div
                                v-for="col in dateColumns"
                                :key="col.key"
                                class="gantt-header-sub-cell"
                            >
                                {{ col.subLabel }}
                            </div>
                        </div>
                    </div>

                    <!-- Gantt Body Canvas (Synced Scroll) -->
                    <div class="gantt-body-scroll-pane" ref="ganttBodyPane" @scroll="syncVerticalScroll('right')">
                        <div class="gantt-body-canvas">
                            <!-- Today Blue Vertical Line -->
                            <div
                                class="today-indicator-line-blue"
                                :style="{ left: todayLeftPercent + '%' }"
                                title="Hôm nay"
                            >
                                <div class="today-marker-badge-blue">Hôm nay</div>
                            </div>

                            <!-- Grid Background Columns -->
                            <div class="gantt-grid-bg">
                                <div
                                    v-for="col in dateColumns"
                                    :key="'bg-' + col.key"
                                    class="gantt-grid-col"
                                ></div>
                            </div>

                            <!-- Task Gantt Rows -->
                            <template v-if="visibleRows.length">
                                <div
                                    v-for="task in visibleRows"
                                    :key="'bar-' + task.id"
                                    class="gantt-row"
                                    :class="{ 'is-hovered': hoveredTaskId === task.id }"
                                >
                                    <div
                                        v-if="!task.isVirtualGroup && !task.isInlineTaskRow"
                                        class="gantt-bar"
                                        :class="[`bar-status-${task.status || 'todo'}`, `bar-type-${task.issue_type || 'task'}`]"
                                        :style="getBarStyle(task)"
                                        @mouseenter="hoveredTaskId = task.id"
                                        @mouseleave="hoveredTaskId = null"
                                        @mousedown="handleBarMouseDown($event, task)"
                                        @click.stop="openDetailDrawer(task)"
                                    >
                                        <!-- Progress fill inside Epic bar -->
                                        <div
                                            v-if="task.issue_type === 'epic'"
                                            class="gantt-bar-progress"
                                            :style="{ width: getEpicProgress(task) + '%' }"
                                        ></div>

                                        <span class="gantt-bar-title">
                                            <i v-if="task.issue_type === 'epic'" class="fa fa-bolt me-1"></i>
                                            {{ getTaskKey(task) }} - {{ task.name }}
                                        </span>

                                        <!-- Drag Resize Handle -->
                                        <div class="gantt-resize-handle" title="Kéo để thay đổi hạn hoàn thành"></div>

                                        <!-- Tooltip Hover -->
                                        <div class="gantt-tooltip">
                                            <div class="tooltip-title">{{ getTaskKey(task) }} - {{ task.name }}</div>
                                            <div class="tooltip-meta">
                                                <div><i class="fa fa-calendar me-1"></i> {{ formatDate(task.start_date || task.created_at) }} → {{ formatDate(task.due_date || task.end_date) }}</div>
                                                <div><i class="fa fa-user me-1"></i> {{ task.assignee?.name || 'Chưa gán' }}</div>
                                                <div><i class="fa fa-flag me-1"></i> Ưu tiên: {{ priorityLabel(task.priority) }}</div>
                                                <div v-if="task.issue_type === 'epic'"><i class="fa fa-check-square me-1"></i> Tiến độ Epic: {{ getEpicProgress(task) }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="gantt-virtual-group-row"></div>
                                </div>
                            </template>
                            <div v-else class="gantt-empty-state">
                                <i class="fa fa-calendar-o"></i>
                                <p>Chưa có dữ liệu timeline cho bộ lọc hiện tại.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. FLOATING BOTTOM CAPSULE CONTROLS -->
        <div class="timeline-floating-toolbar shadow-lg">
            <button @click="scrollToToday" class="tb-btn" title="Cuộn đến hôm nay">
                <span>Today</span>
            </button>
            <div class="tb-divider"></div>
            <button
                @click="viewMode = 'weeks'"
                :class="['tb-btn', { active: viewMode === 'weeks' }]"
            >
                Weeks
            </button>
            <button
                @click="viewMode = 'months'"
                :class="['tb-btn', { active: viewMode === 'months' }]"
            >
                Months
            </button>
            <button
                @click="viewMode = 'quarters'"
                :class="['tb-btn', { active: viewMode === 'quarters' }]"
            >
                Quarters
            </button>
            <div class="tb-divider"></div>
            <button class="tb-btn text-muted" title="Thông tin"><i class="fa fa-info-circle"></i></button>
        </div>

        <!-- 6. JIRA SLIDE-OVER DETAIL DRAWER -->
        <div v-if="showDetailDrawer && selectedTaskForDetail" class="jira-drawer-overlay" @click.self="closeDetailDrawer">
            <div class="jira-drawer-content">
                <!-- Header -->
                <div class="jira-drawer-header">
                    <div class="d-flex align-items-center gap-2">
                        <IssueTypeBadge :type="selectedTaskForDetail.issue_type" />
                        <a :href="`/tasks/${selectedTaskForDetail.id}`" class="font-weight-bold text-dark font-size-14 text-decoration-none">
                            {{ getTaskKey(selectedTaskForDetail) }}
                        </a>
                        <a :href="`/tasks/${selectedTaskForDetail.id}`" target="_blank" class="text-muted small ms-1" title="Mở trang chi tiết đầy đủ">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </div>
                    <button type="button" class="jira-drawer-close" @click="closeDetailDrawer">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="jira-drawer-body">
                    <h2 class="jira-drawer-title mb-3">{{ selectedTaskForDetail.name }}</h2>

                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <span class="text-muted small d-block mb-1">Trạng thái</span>
                            <StatusBadge :status="selectedTaskForDetail.status" />
                        </div>
                        <div>
                            <span class="text-muted small d-block mb-1">Độ ưu tiên</span>
                            <PriorityBadge :priority="selectedTaskForDetail.priority" />
                        </div>
                        <div v-if="selectedTaskForDetail.story_points">
                            <span class="text-muted small d-block mb-1">Story Points</span>
                            <span class="points-badge">{{ selectedTaskForDetail.story_points }} pts</span>
                        </div>
                    </div>

                    <!-- Epic Section -->
                    <div v-if="selectedTaskForDetail.issue_type === 'epic'" class="jira-drawer-section mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h3 class="font-size-14 font-weight-bold m-0 text-dark">
                                <i class="fa fa-tasks text-primary me-1"></i> Các Tasks thuộc Epic này ({{ getEpicChildTasks(selectedTaskForDetail.id).length }})
                            </h3>
                            <span class="badge bg-primary text-white font-size-11">
                                Tiến độ: {{ getEpicProgress(selectedTaskForDetail) }}%
                            </span>
                        </div>

                        <div class="pd-progress-bar mb-3">
                            <div class="pd-progress-fill" :style="{ width: getEpicProgress(selectedTaskForDetail) + '%' }"></div>
                        </div>

                        <div class="epic-child-list mb-3">
                            <div
                                v-for="child in getEpicChildTasks(selectedTaskForDetail.id)"
                                :key="child.id"
                                class="epic-child-row"
                                @click="openDetailDrawer(child)"
                            >
                                <IssueTypeBadge :type="child.issue_type" />
                                <span class="task-key font-size-11 me-1">{{ getTaskKey(child) }}</span>
                                <span class="text-dark flex-grow-1 text-truncate font-size-13">{{ child.name }}</span>
                                <StatusBadge :status="child.status" />
                            </div>
                            <div v-if="!getEpicChildTasks(selectedTaskForDetail.id).length" class="text-muted small p-2 text-center border rounded background-light">
                                Chưa có task nào thuộc Epic này.
                            </div>
                        </div>
                    </div>

                    <!-- Task Section -->
                    <div v-else-if="selectedTaskForDetail.issue_type !== 'subtask'" class="jira-drawer-section mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h3 class="font-size-14 font-weight-bold m-0 text-dark">
                                <i class="fa fa-sitemap text-info me-1"></i> Sub-tasks / Công việc con ({{ getSubtasks(selectedTaskForDetail.id).length }})
                            </h3>
                        </div>

                        <div class="epic-child-list mb-3">
                            <div
                                v-for="sub in getSubtasks(selectedTaskForDetail.id)"
                                :key="sub.id"
                                class="epic-child-row"
                            >
                                <input
                                    type="checkbox"
                                    :checked="['done', 'completed'].includes(sub.status)"
                                    @change="toggleSubtaskStatus(sub)"
                                    class="form-check-input me-2"
                                >
                                <span class="text-dark flex-grow-1 text-truncate font-size-13" :style="['done', 'completed'].includes(sub.status) ? 'text-decoration: line-through; color: #94a3b8;' : ''">
                                    {{ sub.name }}
                                </span>
                                <StatusBadge :status="sub.status" />
                            </div>
                            <div v-if="!getSubtasks(selectedTaskForDetail.id).length" class="text-muted small p-2 text-center border rounded background-light">
                                Chưa có sub-task nào.
                            </div>
                        </div>
                    </div>

                    <!-- Meta Details -->
                    <div class="jira-drawer-details border-top pt-3 mt-3">
                        <div class="detail-row mb-2">
                            <span class="detail-label">Người thực hiện:</span>
                            <UserAvatarPill :name="selectedTaskForDetail.assignee?.name || 'Chưa gán'" />
                        </div>
                        <div class="detail-row mb-2">
                            <span class="detail-label">Người báo cáo:</span>
                            <span class="font-size-13 text-dark">{{ selectedTaskForDetail.reporter?.name || 'Chưa rõ' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <span class="detail-label">Ngày bắt đầu:</span>
                            <span class="font-size-13 text-dark">{{ formatDate(selectedTaskForDetail.start_date || selectedTaskForDetail.created_at) }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <span class="detail-label">Hạn hoàn thành:</span>
                            <span class="font-size-13 text-dark">{{ formatDate(selectedTaskForDetail.due_date || selectedTaskForDetail.end_date) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="jira-drawer-footer">
                    <a :href="`/tasks/${selectedTaskForDetail.id}/edit`" class="tp-btn tp-btn-light me-2">
                        <i class="fa fa-pencil me-1"></i> Chỉnh sửa đầy đủ
                    </a>
                    <a :href="`/tasks/${selectedTaskForDetail.id}`" class="tp-btn tp-btn-primary">
                        <i class="fa fa-eye me-1"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import StatusBadge from '../common/StatusBadge.vue';
import PriorityBadge from '../common/PriorityBadge.vue';
import IssueTypeBadge from '../common/IssueTypeBadge.vue';
import UserAvatarPill from '../common/UserAvatarPill.vue';

const props = defineProps({
    project: { type: Object, required: true },
    tasks: { type: Array, default: () => [] },
    sprints: { type: Array, default: () => [] },
});

// Reactive Task List from DB
const taskList = ref([...props.tasks]);

const searchQuery = ref('');
const filterEpic = ref('');
const filterType = ref('');
const filterStatus = ref('');
const viewMode = ref('quarters');
const hoveredTaskId = ref(null);
const collapsedSet = ref(new Set());

const workspaceOuter = ref(null);
const leftPaneBody = ref(null);
const ganttHeaderRef = ref(null);
const ganttBodyPane = ref(null);

const showDetailDrawer = ref(false);
const selectedTaskForDetail = ref(null);

const showInlineEpicCreate = ref(false);
const newEpicInlineTitle = ref('');

const inlineCreateEpicId = ref(null);
const inlineTaskTitle = ref('');
const inlineTaskInput = ref(null);

watch(inlineCreateEpicId, async (newVal) => {
    if (newVal) {
        await nextTick();
        inlineTaskInput.value?.focus();
    }
});

function toggleInlineTaskForEpic(epicId) {
    if (inlineCreateEpicId.value === epicId) {
        inlineCreateEpicId.value = null;
    } else {
        inlineCreateEpicId.value = epicId;
        inlineTaskTitle.value = '';
        collapsedSet.value.delete(String(epicId));
    }
}

async function handleCreateTaskForEpic(epicId) {
    if (!inlineTaskTitle.value.trim()) return;
    const title = inlineTaskTitle.value.trim();

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                project_id: props.project.id,
                parent_id: epicId,
                name: title,
                status: 'todo',
                issue_type: 'task',
                priority: 'medium'
            })
        });

        const data = await res.json();
        if (data.success || data.id || data.task) {
            const newTask = data.task || data;
            taskList.value.push(newTask);
            inlineTaskTitle.value = '';
            inlineCreateEpicId.value = null;
        } else {
            alert('Không thể tạo Task mới.');
        }
    } catch (err) {
        console.error('Error creating Task for Epic:', err);
    }
}

const epicOptions = computed(() => {
    return (taskList.value || []).filter(t => t.issue_type === 'epic');
});

const teamMembers = computed(() => {
    const map = new Map();
    (taskList.value || []).forEach(t => {
        if (t.assignee && t.assignee.id) {
            map.set(t.assignee.id, t.assignee);
        }
    });
    return Array.from(map.values());
});

const filteredTasksRaw = computed(() => {
    let result = taskList.value || [];

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        result = result.filter(t =>
            (t.name && t.name.toLowerCase().includes(q)) ||
            (t.id && String(t.id).includes(q)) ||
            (t.labels && t.labels.toLowerCase().includes(q))
        );
    }

    if (filterEpic.value) {
        result = result.filter(t => t.parent_id == filterEpic.value || t.id == filterEpic.value);
    }

    if (filterType.value) {
        result = result.filter(t => t.issue_type === filterType.value);
    }

    if (filterStatus.value) {
        const s = filterStatus.value;
        result = result.filter(t => {
            if (s === 'todo') return ['todo', 'backlog', 'open', 'new'].includes(t.status) || !t.status;
            if (s === 'in_progress') return ['in_progress', 'doing', 'review'].includes(t.status);
            if (s === 'done') return ['done', 'completed', 'resolved'].includes(t.status);
            return true;
        });
    }

    return result;
});

// Build 3-level Hierarchy Tree: Epic -> Task -> Subtask
const structuredTreeTasks = computed(() => {
    const rawTasks = filteredTasksRaw.value;

    const epics = rawTasks.filter(t => t.issue_type === 'epic');
    const nonEpics = rawTasks.filter(t => t.issue_type !== 'epic');

    const tree = [];

    const getChildren = (parentId) => {
        return nonEpics.filter(t => t.parent_id == parentId);
    };

    // 1. Epics (Level 0)
    epics.forEach(epic => {
        const children = getChildren(epic.id);
        const processedChildren = [];

        children.forEach(child => {
            const grandChildren = getChildren(child.id);
            processedChildren.push({
                ...child,
                level: 1,
                children: grandChildren.map(gc => ({ ...gc, level: 2, children: [] }))
            });
        });

        tree.push({
            ...epic,
            level: 0,
            children: processedChildren
        });
    });

    // 2. Standalone Tasks (Level 1)
    const standaloneTasks = nonEpics.filter(t => {
        if (!t.parent_id) return true;
        const parent = rawTasks.find(p => p.id == t.parent_id);
        return !parent || parent.issue_type !== 'epic';
    });

    const standaloneTree = [];
    standaloneTasks.forEach(task => {
        const isSubtaskOfAnother = standaloneTasks.some(p => p.id == task.parent_id);
        if (!isSubtaskOfAnother) {
            const subtasks = getChildren(task.id);
            standaloneTree.push({
                ...task,
                level: 1,
                children: subtasks.map(st => ({ ...st, level: 2, children: [] }))
            });
        }
    });

    if (standaloneTree.length > 0) {
        tree.push({
            id: 'unparented-group',
            name: 'Công việc khác / Standalone Tasks',
            issue_type: 'group',
            isVirtualGroup: true,
            level: 0,
            children: standaloneTree
        });
    }

    return tree;
});

const visibleRows = computed(() => {
    const list = [];

    function traverse(nodes) {
        nodes.forEach(node => {
            list.push(node);

            if (inlineCreateEpicId.value === node.id) {
                list.push({
                    id: `inline-create-${node.id}`,
                    isInlineTaskRow: true,
                    parentEpicId: node.id,
                    level: (node.level || 0) + 1
                });
            }

            const isCollapsed = collapsedSet.value.has(String(node.id));
            if (!isCollapsed && node.children && node.children.length > 0) {
                traverse(node.children);
            }
        });
    }

    traverse(structuredTreeTasks.value);
    return list;
});

function hasChildren(task) {
    return task.children && task.children.length > 0;
}

// Calculate Timeline Range
const timeRange = computed(() => {
    let minTime = Infinity;
    let maxTime = -Infinity;

    if (props.project.start_date) {
        minTime = Math.min(minTime, new Date(props.project.start_date).getTime());
    }
    if (props.project.end_date) {
        maxTime = Math.max(maxTime, new Date(props.project.end_date).getTime());
    }

    (taskList.value || []).forEach(t => {
        const s = t.start_date || t.created_at;
        const e = t.due_date || t.end_date || t.start_date || t.created_at;
        if (s) minTime = Math.min(minTime, new Date(s).getTime());
        if (e) maxTime = Math.max(maxTime, new Date(e).getTime());
    });

    const now = new Date().getTime();
    minTime = Math.min(minTime, now - 15 * 86400000);
    maxTime = Math.max(maxTime, now + 60 * 86400000);

    const startDate = new Date(minTime);
    startDate.setDate(1);
    const endDate = new Date(maxTime);
    endDate.setMonth(endDate.getMonth() + 1);
    endDate.setDate(0);

    return {
        start: startDate,
        end: endDate,
        totalMs: endDate.getTime() - startDate.getTime(),
    };
});

// Build Date Header Columns
const dateColumns = computed(() => {
    const cols = [];
    const curr = new Date(timeRange.value.start);
    const end = timeRange.value.end;

    if (viewMode.value === 'weeks') {
        while (curr <= end) {
            cols.push({
                key: curr.getTime(),
                monthLabel: `${String(curr.getMonth() + 1).padStart(2, '0')}/${curr.getFullYear()}`,
                subLabel: `${curr.getDate()}/${curr.getMonth() + 1}`
            });
            curr.setDate(curr.getDate() + 7);
        }
    } else if (viewMode.value === 'quarters') {
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        while (curr <= end) {
            const m = curr.getMonth();
            const startM = monthNames[m];
            const endM = monthNames[Math.min(11, m + 2)];
            cols.push({
                key: curr.getTime(),
                monthLabel: `${curr.getFullYear()}`,
                subLabel: `${startM} - ${endM}`
            });
            curr.setMonth(curr.getMonth() + 3);
        }
    } else {
        const monthNames = ["June", "July", "August", "September", "October", "November", "December", "January", "February", "March", "April", "May"];
        while (curr <= end) {
            const m = curr.getMonth();
            cols.push({
                key: curr.getTime(),
                monthLabel: `${curr.getFullYear()}`,
                subLabel: `${monthNames[m] || (m + 1)}`
            });
            curr.setMonth(curr.getMonth() + 1);
        }
    }

    return cols;
});

const dateHeaderGroups = computed(() => {
    const groups = [];
    let currentLabel = '';
    let count = 0;

    dateColumns.value.forEach(col => {
        if (col.monthLabel !== currentLabel) {
            if (count > 0) {
                groups.push({ label: currentLabel, span: count });
            }
            currentLabel = col.monthLabel;
            count = 1;
        } else {
            count++;
        }
    });
    if (count > 0) {
        groups.push({ label: currentLabel, span: count });
    }

    return groups;
});

const todayLeftPercent = computed(() => {
    const now = new Date().getTime();
    const start = timeRange.value.start.getTime();
    const total = timeRange.value.totalMs;
    if (total <= 0) return 50;
    const pct = ((now - start) / total) * 100;
    return Math.max(0, Math.min(100, pct));
});

function getBarStyle(task) {
    const startRange = timeRange.value.start.getTime();
    const totalMs = timeRange.value.totalMs;

    const taskStart = new Date(task.start_date || task.created_at || Date.now()).getTime();
    let taskEnd = new Date(task.due_date || task.end_date || taskStart + 7 * 86400000).getTime();

    if (taskEnd <= taskStart) {
        taskEnd = taskStart + 7 * 86400000;
    }

    let left = ((taskStart - startRange) / totalMs) * 100;
    let width = ((taskEnd - taskStart) / totalMs) * 100;

    left = Math.max(0, Math.min(95, left));
    width = Math.max(3, Math.min(100 - left, width));

    return {
        left: `${left.toFixed(2)}%`,
        width: `${width.toFixed(2)}%`,
    };
}

function getEpicProgress(epicTask) {
    const children = getEpicChildTasks(epicTask.id);
    if (!children.length) return 0;
    const done = children.filter(c => ['done', 'completed', 'resolved'].includes(c.status)).length;
    return Math.round((done / children.length) * 100);
}

function getEpicSegmentPercent(epicTask, statusGroup) {
    const children = getEpicChildTasks(epicTask.id);
    if (!children.length) return 0;
    let count = 0;
    if (statusGroup === 'in_progress') {
        count = children.filter(c => ['in_progress', 'doing', 'review'].includes(c.status)).length;
    } else if (statusGroup === 'done') {
        count = children.filter(c => ['done', 'completed', 'resolved'].includes(c.status)).length;
    }
    return Math.round((count / children.length) * 100);
}

function getEpicChildTasks(epicId) {
    return (taskList.value || []).filter(t => t.parent_id == epicId);
}

function getSubtasks(taskId) {
    return (taskList.value || []).filter(t => t.parent_id == taskId);
}

function openDetailDrawer(task) {
    if (!task || task.isVirtualGroup) return;
    selectedTaskForDetail.value = task;
    showDetailDrawer.value = true;
}

function closeDetailDrawer() {
    showDetailDrawer.value = false;
    selectedTaskForDetail.value = null;
}

function toggleCollapse(id) {
    const strId = String(id);
    if (collapsedSet.value.has(strId)) {
        collapsedSet.value.delete(strId);
    } else {
        collapsedSet.value.add(strId);
    }
}

function expandAll() {
    collapsedSet.value.clear();
}

function collapseAll() {
    (taskList.value || []).forEach(t => {
        collapsedSet.value.add(String(t.id));
    });
    collapsedSet.value.add('unparented-group');
}

async function handleCreateInlineEpic() {
    if (!newEpicInlineTitle.value.trim()) return;
    const title = newEpicInlineTitle.value.trim();

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                project_id: props.project.id,
                name: title,
                status: 'todo',
                issue_type: 'epic',
                priority: 'high'
            })
        });

        const data = await res.json();
        if (data.success || data.id || data.task) {
            const newEpic = data.task || data;
            taskList.value.push(newEpic);
            newEpicInlineTitle.value = '';
            showInlineEpicCreate.value = false;
        } else {
            alert('Không thể tạo Epic mới.');
        }
    } catch (err) {
        console.error('Error creating Epic:', err);
    }
}

// Drag & Resize Gantt Bar Handlers
let activeDragTask = null;
let isResizing = false;
let startMouseX = 0;

function handleBarMouseDown(event, task) {
    if (task.isVirtualGroup) return;
    event.stopPropagation();

    isResizing = event.target.classList.contains('gantt-resize-handle');
    activeDragTask = task;
    startMouseX = event.clientX;

    window.addEventListener('mousemove', handleBarMouseMove);
    window.addEventListener('mouseup', handleBarMouseUp);
}

function handleBarMouseMove(event) {
    if (!activeDragTask) return;
    const deltaX = event.clientX - startMouseX;
    const canvasWidth = ganttBodyPane.value ? ganttBodyPane.value.scrollWidth : 1000;
    const deltaMs = (deltaX / canvasWidth) * timeRange.value.totalMs;

    if (isResizing) {
        const currentEnd = new Date(activeDragTask.due_date || activeDragTask.end_date || Date.now()).getTime();
        activeDragTask.due_date = new Date(currentEnd + deltaMs).toISOString().split('T')[0];
    } else {
        const currentStart = new Date(activeDragTask.start_date || activeDragTask.created_at || Date.now()).getTime();
        const currentEnd = new Date(activeDragTask.due_date || activeDragTask.end_date || currentStart + 7 * 86400000).getTime();

        activeDragTask.start_date = new Date(currentStart + deltaMs).toISOString().split('T')[0];
        activeDragTask.due_date = new Date(currentEnd + deltaMs).toISOString().split('T')[0];
    }
    startMouseX = event.clientX;
}

async function handleBarMouseUp() {
    window.removeEventListener('mousemove', handleBarMouseMove);
    window.removeEventListener('mouseup', handleBarMouseUp);

    if (!activeDragTask) return;
    const taskToSave = activeDragTask;
    activeDragTask = null;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        await fetch(`/tasks/${taskToSave.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                start_date: taskToSave.start_date,
                due_date: taskToSave.due_date
            })
        });
    } catch (err) {
        console.error('Error updating task dates:', err);
    }
}

async function toggleSubtaskStatus(subtask) {
    const oldStatus = subtask.status;
    const newStatus = ['done', 'completed'].includes(oldStatus) ? 'todo' : 'done';
    subtask.status = newStatus;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        await fetch(`/tasks/${subtask.id}/move`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        });
    } catch (err) {
        subtask.status = oldStatus;
    }
}

// Scroll Syncing Logic
function syncVerticalScroll(source) {
    if (source === 'left' && leftPaneBody.value && ganttBodyPane.value) {
        ganttBodyPane.value.scrollTop = leftPaneBody.value.scrollTop;
    } else if (source === 'right' && ganttBodyPane.value && leftPaneBody.value) {
        leftPaneBody.value.scrollTop = ganttBodyPane.value.scrollTop;
    }
}

function getTaskKey(task) {
    if (task.issue_key) return task.issue_key;
    const code = props.project.project_code || 'EDU';
    return `${code}-${task.id}`;
}

function getInitials(name) {
    if (!name) return 'TP';
    const parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function formatDate(dateStr) {
    if (!dateStr) return 'Chưa đặt';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return 'Chưa đặt';
    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
}

function priorityLabel(p) {
    return { high: 'Cao', medium: 'Trung bình', low: 'Thấp' }[p] || p || 'Thấp';
}

function scrollToToday() {
    if (!ganttBodyPane.value) return;
    const pane = ganttBodyPane.value;
    const maxScroll = pane.scrollWidth - pane.clientWidth;
    const target = (todayLeftPercent.value / 100) * pane.scrollWidth - pane.clientWidth / 2;
    pane.scrollTo({
        left: Math.max(0, Math.min(maxScroll, target)),
        behavior: 'smooth',
    });
}

onMounted(() => {
    setTimeout(() => {
        scrollToToday();
    }, 300);
});
</script>

<style scoped>
.task-timeline-vue {
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    width: 100%;
}

/* Team Stack */
.timeline-team-stack {
    display: flex;
    align-items: center;
}

.team-avatar-item {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #2563eb;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
    margin-left: -8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.team-avatar-item:first-child {
    margin-left: 0;
}

.team-avatar-item.more {
    background: #64748b;
}

/* Timeline Workspace Outer Container with Unified Horizontal Scrollbar */
.timeline-workspace-outer {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    background: #ffffff;
    border: 1px solid #dfe1e6;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(9, 30, 66, 0.08);
}

.timeline-workspace-inner {
    display: flex;
    width: max-content;
    min-width: 100%;
    height: calc(100vh - 210px);
    min-height: 520px;
}

/* Left Pane */
.timeline-left-pane {
    width: 460px;
    flex-shrink: 0;
    border-right: 1px solid #dfe1e6;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    z-index: 10;
}

.pane-header {
    height: 44px;
    padding: 0 10px;
    background: #f4f5f7;
    border-bottom: 1px solid #dfe1e6;
    display: flex;
    align-items: center;
    font-size: 12px;
    font-weight: 700;
    color: #5e6c84;
}

.pane-body {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: thin;
}

.timeline-task-row {
    height: 44px;
    padding: 0 10px;
    border-bottom: 1px solid #f4f5f7;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s ease;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
}

.timeline-task-row:hover {
    background: #f4f5f7;
}

.timeline-task-row.is-selected {
    background: #deebff !important;
    border-left: 3px solid #0052cc;
}

.timeline-task-row.is-epic {
    font-weight: 700;
    background: #faf5ff;
    border-bottom: 1px solid #e9d5ff;
}

.timeline-task-row.is-virtual {
    font-weight: 700;
    background: #f4f5f7;
}

.tree-indent-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
}

.collapse-btn {
    border: none;
    background: none;
    color: #5e6c84;
    font-size: 11px;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
}

.collapse-btn:hover {
    background: #ebecf0;
}

.tree-leaf-spacer {
    width: 16px;
    display: inline-block;
}

.task-key-blue {
    font-size: 12px;
    font-weight: 600;
    color: #0052cc;
    text-decoration: underline;
    cursor: pointer;
}

.task-key-blue:hover {
    color: #0747a6;
}

.task-name-text {
    color: #172b4d;
    font-size: 13px;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.epic-status-segment-bar {
    height: 8px;
    width: 90px;
    background: #dfe1e6;
    border-radius: 4px;
    display: flex;
    overflow: hidden;
}

.seg-in-progress {
    background: #388bff;
    height: 100%;
}

.seg-done {
    background: #36b37e;
    height: 100%;
}

.timeline-empty-left {
    padding: 30px;
    text-align: center;
    color: #5e6c84;
    font-size: 13px;
}

/* Right Pane: Gantt Chart */
.timeline-right-pane {
    width: 1100px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    background: #ffffff;
    position: relative;
    overflow: hidden;
}

/* Two-Tier Sticky Date Axis Header */
.gantt-header-container {
    height: 44px;
    background: #f4f5f7;
    border-bottom: 1px solid #dfe1e6;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-width: 100%;
}

.gantt-header-top-row {
    height: 22px;
    display: flex;
    border-bottom: 1px solid #dfe1e6;
}

.gantt-header-top-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: #172b4d;
    border-right: 1px solid #dfe1e6;
}

.gantt-header-bottom-row {
    height: 22px;
    display: flex;
}

.gantt-header-sub-cell {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    color: #5e6c84;
    border-right: 1px solid #dfe1e6;
}

.gantt-body-scroll-pane {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    position: relative;
}

.gantt-body-canvas {
    min-width: 100%;
    position: relative;
    min-height: 100%;
}

.gantt-grid-bg {
    position: absolute;
    inset: 0;
    display: flex;
    pointer-events: none;
    z-index: 1;
}

.gantt-grid-col {
    flex: 1;
    border-right: 1px solid #f4f5f7;
    height: 100%;
}

.today-indicator-line-blue {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #0052cc;
    z-index: 15;
    pointer-events: none;
}

.today-marker-badge-blue {
    position: absolute;
    top: 2px;
    left: -26px;
    background: #0052cc;
    color: #ffffff;
    font-size: 9.5px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0, 82, 204, 0.4);
}

.gantt-row {
    height: 44px;
    position: relative;
    border-bottom: 1px solid #f4f5f7;
    z-index: 5;
    display: flex;
    align-items: center;
}

.gantt-row.is-hovered {
    background: rgba(9, 30, 66, 0.04);
}

.gantt-virtual-group-row {
    height: 100%;
    width: 100%;
    background: #f4f5f7;
}

.gantt-bar {
    position: absolute;
    height: 26px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    padding: 0 8px;
    cursor: grab;
    box-shadow: 0 1px 3px rgba(9, 30, 66, 0.1);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    user-select: none;
    overflow: hidden;
}

.gantt-bar:active {
    cursor: grabbing;
}

.bar-type-epic {
    height: 28px;
    border-radius: 6px;
    background: #5e6c84;
}

.bar-status-done { background: #36b37e; }
.bar-status-in_progress { background: #388bff; }
.bar-status-todo { background: #a5adba; }

.gantt-resize-handle {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 8px;
    cursor: e-resize;
    background: rgba(255, 255, 255, 0.4);
    z-index: 10;
}

.gantt-bar-progress {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 6px 0 0 6px;
    pointer-events: none;
}

.gantt-bar-title {
    font-size: 11px;
    font-weight: 600;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    position: relative;
    z-index: 2;
}

/* Gantt Tooltip */
.gantt-tooltip {
    display: none;
    position: absolute;
    bottom: 110%;
    left: 50%;
    transform: translateX(-50%);
    background: #091e42;
    color: #ffffff;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 11px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(9, 30, 66, 0.3);
    z-index: 30;
    pointer-events: none;
}

.gantt-bar:hover .gantt-tooltip {
    display: block;
}

.tooltip-title {
    font-weight: 700;
    margin-bottom: 4px;
}

.tooltip-meta {
    color: #ebecf0;
    font-size: 10.5px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.gantt-empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #5e6c84;
}

.gantt-empty-state i {
    font-size: 32px;
    margin-bottom: 8px;
}

/* Floating Toolbar */
.timeline-floating-toolbar {
    position: sticky;
    bottom: 16px;
    margin-left: auto;
    width: fit-content;
    background: #ffffff;
    border: 1px solid #dfe1e6;
    border-radius: 20px;
    padding: 4px 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 16px rgba(9, 30, 66, 0.15);
    z-index: 50;
}

.tb-btn {
    border: none;
    background: transparent;
    padding: 4px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    color: #42526e;
    cursor: pointer;
    transition: all 0.15s ease;
}

.tb-btn:hover {
    background: #ebecf0;
    color: #091e42;
}

.tb-btn.active {
    background: #ffffff;
    border: 2px solid #0052cc;
    color: #0052cc;
    font-weight: 700;
}

.tb-divider {
    width: 1px;
    height: 16px;
    background: #dfe1e6;
    margin: 0 2px;
}

/* Jira Slide-Over Detail Drawer */
.jira-drawer-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(9, 30, 66, 0.4);
    backdrop-filter: blur(4px);
    z-index: 1050;
    display: flex;
    justify-content: flex-end;
}

.jira-drawer-content {
    width: 520px;
    max-width: 90vw;
    height: 100%;
    background: #ffffff;
    box-shadow: -8px 0 32px rgba(9, 30, 66, 0.25);
    display: flex;
    flex-direction: column;
    animation: slideInRight 0.25s ease-out;
}

@keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

.jira-drawer-header {
    height: 56px;
    padding: 0 20px;
    border-bottom: 1px solid #dfe1e6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f4f5f7;
}

.jira-drawer-close {
    border: none;
    background: transparent;
    font-size: 18px;
    color: #5e6c84;
    cursor: pointer;
}

.jira-drawer-close:hover {
    color: #de350b;
}

.jira-drawer-body {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
}

.jira-drawer-title {
    font-size: 20px;
    font-weight: 700;
    color: #172b4d;
    line-height: 1.3;
}

.epic-child-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.epic-child-row {
    background: #ffffff;
    border: 1px solid #dfe1e6;
    border-radius: 6px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.epic-child-row:hover {
    background: #f4f5f7;
    border-color: #0052cc;
}

.jira-drawer-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.detail-row {
    display: grid;
    grid-template-columns: 130px 1fr;
    align-items: center;
}

.detail-label {
    font-size: 12.5px;
    font-weight: 600;
    color: #5e6c84;
}

.jira-drawer-footer {
    padding: 16px 20px;
    border-top: 1px solid #dfe1e6;
    background: #f4f5f7;
    display: flex;
    justify-content: flex-end;
}

.epic-quick-add-btn {
    width: 22px;
    height: 22px;
    border-radius: 4px;
    border: 1px dashed #8b5cf6;
    background: #f5f3ff;
    color: #7c3aed;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 11px;
    padding: 0;
    transition: all 0.15s ease;
    flex-shrink: 0;
}

.epic-quick-add-btn:hover {
    background: #7c3aed;
    color: #ffffff !important;
    border-style: solid;
    transform: scale(1.08);
}

.timeline-task-row.is-inline-row {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
</style>