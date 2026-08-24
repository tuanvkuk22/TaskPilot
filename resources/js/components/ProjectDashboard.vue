<template>
    <div class="project-dashboard-vue">
        <!-- HEADER -->
        <div class="pd-header">
            <div class="pd-header-left">
                <div class="pd-breadcrumb">
                    <a href="/projects">Dự án</a>
                    <span>/</span>
                    <span>{{ project.name }}</span>
                </div>
                <div class="pd-title-row">
                    <h1 class="pd-title">{{ project.name }}</h1>
                    <span class="pd-project-key">{{ project.project_code }}</span>
                    <span class="pd-project-status">{{ project.status }}</span>
                    <a :href="`/projects/${project.id}/edit`" class="pd-settings-btn" title="Cài đặt dự án">
                        <i class="fa fa-cog"></i>
                    </a>
                </div>
                <div class="pd-meta">
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
        </div>

        <!-- PROJECT NAV -->
        <div class="pd-project-nav">
            <a href="/projects" class="pd-nav-item">Dự án</a>
            <a href="/projects" class="pd-nav-item active">Tổng quan</a>
            <a :href="`/projects/${project.id}/board`" class="pd-nav-item">Bảng</a>
            <a :href="`/projects/${project.id}/members`" class="pd-nav-item">Thành viên</a>
            <a :href="`/projects/${project.id}/roles`" class="pd-nav-item">Vai trò &amp; Quyền</a>
            <a href="#" class="pd-nav-item">Phân quyền</a>
            <a href="#" class="pd-nav-item">Backlog</a>
            <a href="#" class="pd-nav-item">Development</a>
            <a href="#" class="pd-nav-item">Timeline</a>
            <a href="#" class="pd-nav-item">Tài liệu</a>
            <a href="#" class="pd-nav-item">Form</a>
            <a href="#" class="pd-nav-item">Lịch họp</a>
            <a href="#" class="pd-nav-item">Chat</a>
        </div>

        <!-- STATS ROW -->
        <div class="pd-stats">
            <div class="pd-stat-card">
                <div class="pd-stat-label">Tổng task</div>
                <div class="pd-stat-value">{{ stats.total }}</div>
            </div>
            <div class="pd-stat-card pd-stat-done">
                <div class="pd-stat-label">Hoàn thành</div>
                <div class="pd-stat-value">{{ stats.done }}</div>
                <div class="pd-stat-percent">{{ stats.total ? Math.round(stats.done / stats.total * 100) : 0 }}%</div>
            </div>
            <div class="pd-stat-card pd-stat-progress">
                <div class="pd-stat-label">Đang làm</div>
                <div class="pd-stat-value">{{ stats.inProgress }}</div>
                <div class="pd-stat-percent">{{ stats.total ? Math.round(stats.inProgress / stats.total * 100) : 0 }}%</div>
            </div>
            <div class="pd-stat-card pd-stat-overdue">
                <div class="pd-stat-label">Quá hạn</div>
                <div class="pd-stat-value">{{ stats.overdue }}</div>
                <div class="pd-stat-percent">{{ stats.total ? Math.round(stats.overdue / stats.total * 100) : 0 }}%</div>
            </div>
        </div>

        <!-- MAIN GRID -->
        <div class="pd-grid">
            <!-- LEFT COLUMN -->
            <div class="pd-main">
                <!-- PROGRESS -->
                <div class="pd-jira-card">
                    <div class="pd-jira-card-header">
                        <h2 class="pd-jira-card-title">Tiến độ dự án</h2>
                    </div>
                    <div class="pd-jira-card-body">
                        <div class="pd-progress-bar">
                            <div class="pd-progress-fill" :style="{ width: progressPercent + '%' }"></div>
                        </div>
                        <div class="pd-progress-labels">
                            <span>Start {{ formatDate(project.start_date) }}</span>
                            <span>Today {{ todayLabel }}</span>
                            <span>End {{ formatDate(project.end_date) }}</span>
                        </div>
                        <div class="pd-progress-percent">{{ progressPercent }}%</div>
                    </div>
                </div>

                <!-- RECENT TASKS -->
                <div class="pd-jira-card">
                    <div class="pd-jira-card-header">
                        <h2 class="pd-jira-card-title">Task gần đây</h2>
                        <a href="/tasks" class="pd-link">Xem tất cả</a>
                    </div>
                    <div class="pd-jira-card-body">
                        <div v-if="recentTasks.length" class="pd-task-list">
                            <div v-for="task in recentTasks" :key="task.id" class="pd-task-row">
                                <div class="pd-task-key">{{ task.issue_key || 'TP-' + padId(task.id) }}</div>
                                <div class="pd-task-name">{{ task.name }}</div>
                                <span class="pd-priority-badge" :class="'pd-priority-' + (task.priority || 'low')">
                                    {{ priorityLabel(task.priority) }}
                                </span>
                                <span class="pd-task-assignee">{{ task.assignee?.name || 'Chưa gán' }}</span>
                                <span class="pd-task-date">{{ formatDate(task.due_date) }}</span>
                            </div>
                        </div>
                        <div v-else class="pd-empty">Chưa có task nào.</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="pd-sidebar">
                <!-- AI INSIGHTS -->
                <div class="pd-jira-card">
                    <div class="pd-jira-card-header">
                        <h2 class="pd-jira-card-title">AI Project Insights</h2>
                    </div>
                    <div class="pd-jira-card-body">
                        <div class="pd-insight-item">
                            <i class="fa fa-bolt"></i>
                            <span>{{ risks.length }} rủi ro cần chú ý</span>
                        </div>
                        <div v-if="risks.length" class="pd-risk-list">
                            <div v-for="risk in risks" :key="risk" class="pd-risk-item">
                                • {{ risk }}
                            </div>
                        </div>
                        <div v-else class="pd-empty-small">Không có rủi ro đáng kể.</div>
                        <a href="#" class="pd-link pd-link-primary">Xem phân tích →</a>
                    </div>
                </div>

                <!-- WORK DISTRIBUTION -->
                <div class="pd-jira-card">
                    <div class="pd-jira-card-header">
                        <h2 class="pd-jira-card-title">Phân bố công việc</h2>
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

                <!-- RECENT ACTIVITY -->
                <div class="pd-jira-card">
                    <div class="pd-jira-card-header">
                        <h2 class="pd-jira-card-title">Hoạt động gần đây</h2>
                    </div>
                    <div class="pd-jira-card-body">
                        <div v-if="recentActivity.length" class="pd-activity-list">
                            <div v-for="act in recentActivity" :key="act.id" class="pd-activity-item">
                                <div class="pd-activity-text">{{ act.text }}</div>
                                <div class="pd-activity-time">{{ formatDate(act.created_at) }}</div>
                            </div>
                        </div>
                        <div v-else class="pd-empty-small">Chưa có hoạt động.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    project: { type: Object, required: true },
    tasks: { type: Array, default: () => [] },
    comments: { type: Array, default: () => [] },
});

const today = new Date();

const stats = computed(() => {
    const all = props.tasks || [];
    const done = all.filter(t => t.status === 'done').length;
    const inProgress = all.filter(t => t.status === 'in_progress').length;
    const overdue = all.filter(t => {
        if (!t.due_date) return false;
        const due = new Date(t.due_date);
        return due < today && t.status !== 'done';
    }).length;
    return { total: all.length, done, inProgress, overdue };
});

const progressPercent = computed(() => {
    if (!stats.value.total) return 0;
    return Math.round((stats.value.done / stats.value.total) * 100);
});

const todayLabel = computed(() => {
    const d = today;
    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}`;
});

const recentTasks = computed(() => {
    return (props.tasks || [])
        .slice()
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 5);
});

const risks = computed(() => {
    const list = [];
    const overdue = props.tasks?.filter(t => {
        if (!t.due_date) return false;
        const due = new Date(t.due_date);
        return due < today && t.status !== 'done';
    }) || [];

    if (overdue.length > 0) {
        list.push(`${overdue.length} task có nguy cơ trễ deadline`);
    }

    const high = props.tasks?.filter(t => t.priority === 'high' && t.status !== 'done').length || 0;
    if (high > 0) {
        list.push(`${high} task ưu tiên cao đang thực hiện`);
    }

    return list;
});

const workDistribution = computed(() => {
    const labels = { backend: 'Backend', frontend: 'Frontend', ai: 'AI' };
    const counts = { backend: 0, frontend: 0, ai: 0 };
    const total = props.tasks?.length || 0;

    (props.tasks || []).forEach(task => {
        const name = ((task.name || '') + ' ' + (task.labels || '')).toLowerCase();
        if (name.includes('backend') || name.includes('api') || name.includes('server')) {
            counts.backend++;
        } else if (name.includes('frontend') || name.includes('ui') || name.includes('css')) {
            counts.frontend++;
        } else if (name.includes('ai') || name.includes('ml') || name.includes('model')) {
            counts.ai++;
        }
    });

    return Object.keys(labels).map(key => ({
        label: labels[key],
        count: counts[key],
        percent: total ? Math.round((counts[key] / total) * 100) : 0,
    }));
});

const recentActivity = computed(() => {
    const acts = [];
    (props.comments || []).slice(0, 5).forEach(c => {
        acts.push({
            id: 'comment-' + c.id,
            text: `${c.user?.name || 'Ai đó'} bình luận trên ${c.commentable_type || 'dự án'}`,
            created_at: c.created_at,
        });
    });
    return acts.slice(0, 5);
});

function formatDate(value) {
    if (!value) return 'Chưa đặt';
    const d = new Date(value);
    if (isNaN(d)) return 'Chưa đặt';
    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}`;
}

function formatNumber(value) {
    if (!value && value !== 0) return '0';
    return Number(value).toLocaleString('vi-VN');
}

function padId(id) {
    return String(id).padStart(5, '0');
}

function priorityLabel(p) {
    const map = { high: 'Cao', medium: 'Trung bình', low: 'Thấp' };
    return map[p] || p || 'Thấp';
}
</script>
<style scoped>
.project-dashboard-vue {
    width: 100%;
    min-width: 0;
}

/* HEADER */
.pd-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 24px;
}

.pd-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 12px;
    color: #64748b;
}

.pd-breadcrumb a {
    color: #2563eb;
    text-decoration: none;
}

.pd-title {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #172033;
}

.pd-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 4px;
}

.pd-project-key {
    padding: 3px 8px;
    background: #f1f5f9;
    color: #475569;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.pd-project-status {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    background: #dcfce7;
    color: #16a34a;
}

.pd-settings-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    color: #64748b;
    text-decoration: none;
    transition: all 0.15s ease;
}

.pd-settings-btn:hover {
    background: #f1f5f9;
    color: #2563eb;
}

.pd-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 8px;
    font-size: 13px;
    color: #64748b;
}

.pd-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* PROJECT NAV */
.pd-project-nav {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 24px;
    padding: 6px 8px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow-x: auto;
}

.pd-nav-item {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s ease;
}

.pd-nav-item:hover {
    background: #f1f5f9;
    color: #172033;
}

.pd-nav-item.active {
    background: #2563eb;
    color: #fff;
    font-weight: 600;
}

/* STATS */
.pd-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.pd-stat-card {
    padding: 16px 18px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.pd-stat-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 4px;
}

.pd-stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #172033;
    line-height: 1.2;
}

.pd-stat-percent {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

.pd-stat-done { border-top: 3px solid #22c55e; }
.pd-stat-progress { border-top: 3px solid #2563eb; }
.pd-stat-overdue { border-top: 3px solid #ef4444; }

/* GRID */
.pd-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 24px;
}

.pd-main {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.pd-sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* CARD */
.pd-jira-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.pd-jira-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.pd-jira-card-title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #172033;
}

.pd-jira-card-body {
    padding: 16px 18px;
}

/* PROGRESS */
.pd-progress-bar {
    width: 100%;
    height: 12px;
    background: #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 8px;
}

.pd-progress-fill {
    height: 100%;
    background: #2563eb;
    border-radius: 6px;
    transition: width 0.3s ease;
}

.pd-progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
}

.pd-progress-percent {
    font-size: 24px;
    font-weight: 700;
    color: #2563eb;
    text-align: right;
}

/* TASK LIST */
.pd-task-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pd-task-row {
    display: grid;
    grid-template-columns: 80px 1fr auto auto auto;
    gap: 12px;
    align-items: center;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
}

.pd-task-key {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
}

.pd-task-name {
    font-size: 13px;
    color: #172033;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pd-priority-badge {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.pd-priority-high { background: #fef2f2; color: #dc2626; }
.pd-priority-medium { background: #fefce8; color: #ca8a04; }
.pd-priority-low { background: #f0fdf4; color: #16a34a; }

.pd-task-assignee {
    font-size: 12px;
    color: #64748b;
    white-space: nowrap;
}

.pd-task-date {
    font-size: 12px;
    color: #94a3b8;
    white-space: nowrap;
}

/* INSIGHTS */
.pd-insight-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #172033;
    margin-bottom: 10px;
}

.pd-insight-item i {
    color: #2563eb;
}

.pd-risk-list {
    margin-bottom: 12px;
}

.pd-risk-item {
    font-size: 13px;
    color: #475569;
    padding: 4px 0;
}

/* DISTRIBUTION */
.pd-dist-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pd-dist-row {
    display: grid;
    grid-template-columns: 80px 40px 1fr;
    gap: 12px;
    align-items: center;
}

.pd-dist-label {
    font-size: 13px;
    color: #334155;
    font-weight: 500;
}

.pd-dist-value {
    font-size: 13px;
    color: #172033;
    font-weight: 600;
    text-align: right;
}

.pd-dist-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.pd-dist-fill {
    height: 100%;
    background: #2563eb;
    border-radius: 4px;
    transition: width 0.3s ease;
}

/* ACTIVITY */
.pd-activity-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pd-activity-item {
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
}

.pd-activity-item:last-child {
    border-bottom: none;
}

.pd-activity-text {
    font-size: 13px;
    color: #334155;
    line-height: 1.4;
}

.pd-activity-time {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 2px;
}

/* UTILS */
.pd-empty {
    padding: 20px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}

.pd-empty-small {
    padding: 8px 0;
    color: #64748b;
    font-size: 12px;
}

.pd-link {
    font-size: 12px;
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.pd-link-primary {
    font-weight: 600;
}

.pd-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 38px;
    padding: 8px 14px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
}

.pd-btn-primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .pd-grid {
        grid-template-columns: 1fr;
    }

    .pd-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .pd-stats {
        grid-template-columns: 1fr;
    }

    .pd-task-row {
        grid-template-columns: 1fr;
        gap: 6px;
    }
}
</style>
