<template>
    <div class="backlog-container">
        <div class="jira-workspace">
            <div class="jira-workspace-heading">
                <div>
                    <span class="jira-eyebrow">{{ project.project_code }}</span>
                    <h1>{{ project.name }} / Backlog</h1>
                    <p>Quản lý backlog, tạo sprint và theo dõi phạm vi phát hành.</p>
                </div>

                <div class="jira-view-links">
                    <a href="/projects/{{ project.id }}/board">Board</a>
                    <a class="active" href="/projects/{{ project.id }}/backlog">Backlog</a>
                    <a href="/projects/{{ project.id }}/timeline">Timeline</a>
                    <a href="/projects/{{ project.id }}/roadmap">Roadmap</a>
                    <button class="btn btn-primary btn-sm" @click="openCreateSprintModal">
                        <i class="fa fa-plus"></i> Sprint mới
                    </button>
                </div>
            </div>

            <div class="backlog-view">
                <!-- Backlog Section -->
                <div class="backlog-section">
                    <h3 class="backlog-section-title">
                        <i class="fa fa-inbox"></i> Backlog
                        <span class="badge">{{ backlogTasks.length }}</span>
                    </h3>
                    <div
                        class="backlog-list"
                        @drop="handleDropToBacklog"
                        @dragover.prevent
                        @dragenter.prevent
                    >
                        <div
                            v-for="task in backlogTasks"
                            :key="task.id"
                            class="backlog-item"
                            draggable="true"
                            @dragstart="startDrag($event, task, 'backlog')"
                        >
                            <div class="backlog-item-handle">
                                <i class="fa fa-bars"></i>
                            </div>
                            <div class="backlog-item-content">
                                <span class="task-key">{{ task.issue_key || `TP-${String(task.id).padStart(5, '0')}` }}</span>
                                <a :href="`/tasks/${task.id}`" class="task-title">{{ task.name }}</a>
                                <div class="task-meta">
                                    <span v-if="task.assignee" class="task-assignee">
                                        <img :src="task.assignee.avatar" :alt="task.assignee.name" :title="task.assignee.name">
                                    </span>
                                    <span v-if="task.story_points" class="task-story-points">{{ task.story_points }} pts</span>
                                    <span class="task-type" :class="`type-${task.issue_type}`">{{ task.issue_type }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="backlogTasks.length === 0" class="empty-state">
                            <p>Không có task trong backlog</p>
                        </div>
                    </div>
                </div>

                <!-- Sprints Section -->
                <div v-for="sprint in sprints" :key="sprint.id" class="backlog-section sprint-section">
                    <div class="sprint-header" @click="toggleSprintCollapse(sprint.id)">
                        <i :class="collapsedSprints.includes(sprint.id) ? 'fa fa-chevron-right' : 'fa fa-chevron-down'"></i>
                        <h3 class="sprint-title">
                            {{ sprint.name }}
                            <span class="badge">{{ sprint.tasks.length }}</span>
                        </h3>
                        <div class="sprint-meta">
                            <span v-if="sprint.starts_at" class="sprint-date">
                                <i class="fa fa-calendar"></i> {{ formatDate(sprint.starts_at) }}
                            </span>
                            <span class="sprint-status" :class="`status-${sprint.status}`">
                                {{ sprint.status }}
                            </span>
                        </div>
                        <div class="sprint-actions">
                            <button v-if="sprint.status === 'draft'" class="btn btn-xs btn-primary" @click.stop="startSprint(sprint)">
                                Bắt đầu
                            </button>
                            <button v-if="sprint.status === 'active'" class="btn btn-xs btn-success" @click.stop="completeSprint(sprint)">
                                Hoàn thành
                            </button>
                        </div>
                    </div>

                    <div v-if="!collapsedSprints.includes(sprint.id)" class="backlog-list" @drop="handleDropToSprint($event, sprint)" @dragover.prevent @dragenter.prevent>
                        <div
                            v-for="task in sprint.tasks"
                            :key="task.id"
                            class="backlog-item"
                            draggable="true"
                            @dragstart="startDrag($event, task, sprint.id)"
                        >
                            <div class="backlog-item-handle">
                                <i class="fa fa-bars"></i>
                            </div>
                            <div class="backlog-item-content">
                                <span class="task-key">{{ task.issue_key || `TP-${String(task.id).padStart(5, '0')}` }}</span>
                                <a :href="`/tasks/${task.id}`" class="task-title">{{ task.name }}</a>
                                <div class="task-meta">
                                    <span v-if="task.assignee" class="task-assignee">
                                        <img :src="task.assignee.avatar" :alt="task.assignee.name" :title="task.assignee.name">
                                    </span>
                                    <span v-if="task.story_points" class="task-story-points">{{ task.story_points }} pts</span>
                                    <span class="task-type" :class="`type-${task.issue_type}`">{{ task.issue_type }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="sprint.tasks.length === 0" class="empty-state">
                            <p>Không có task trong sprint này</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Sprint Modal -->
        <div v-if="showCreateSprintModal" class="modal-overlay" @click="closeCreateSprintModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h3>Tạo Sprint mới</h3>
                    <button class="btn-close" @click="closeCreateSprintModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên Sprint</label>
                        <input v-model="newSprint.name" type="text" class="form-control" placeholder="VD: Sprint 1">
                    </div>
                    <div class="form-group">
                        <label>Mục tiêu</label>
                        <textarea v-model="newSprint.goal" class="form-control" rows="3" placeholder="Mục tiêu của sprint"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Ngày bắt đầu</label>
                            <input v-model="newSprint.starts_at" type="date" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Ngày kết thúc</label>
                            <input v-model="newSprint.ends_at" type="date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeCreateSprintModal">Hủy</button>
                    <button class="btn btn-primary" @click="createSprint">Tạo Sprint</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        project: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            sprints: [],
            backlogTasks: [],
            tasks: [],
            showCreateSprintModal: false,
            collapsedSprints: [],
            newSprint: {
                name: '',
                goal: '',
                starts_at: '',
                ends_at: ''
            },
            draggedTask: null,
            draggedFrom: null
        };
    },
    computed: {
        completedSprintCount() {
            return this.sprints.filter(s => s.status === 'completed').length;
        }
    },
    mounted() {
        this.loadSprints();
        this.loadTasks();
    },
    methods: {
        async loadSprints() {
            try {
                const response = await fetch(`/api/projects/${this.project.id}/sprints`);
                const data = await response.json();
                this.sprints = data;
            } catch (error) {
                console.error('Error loading sprints:', error);
            }
        },
        async loadTasks() {
            try {
                const response = await fetch(`/api/projects/${this.project.id}/tasks`);
                const data = await response.json();
                this.tasks = data;
                this.filterBacklogTasks();
            } catch (error) {
                console.error('Error loading tasks:', error);
            }
        },
        filterBacklogTasks() {
            this.backlogTasks = this.tasks.filter(task => !task.sprint_id);
        },
        startDrag(e, task, from) {
            this.draggedTask = task;
            this.draggedFrom = from;
            e.dataTransfer.effectAllowed = 'move';
        },
        handleDropToBacklog(e) {
            e.preventDefault();
            if (this.draggedTask && this.draggedFrom !== 'backlog') {
                this.moveTaskToBacklog(this.draggedTask);
            }
        },
        handleDropToSprint(e, sprint) {
            e.preventDefault();
            if (this.draggedTask) {
                this.moveTaskToSprint(this.draggedTask, sprint);
            }
        },
        async moveTaskToBacklog(task) {
            try {
                await fetch(`/api/tasks/${task.id}/sprint`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ sprint_id: null })
                });
                await this.loadSprints();
                await this.loadTasks();
            } catch (error) {
                console.error('Error moving task:', error);
            }
        },
        async moveTaskToSprint(task, sprint) {
            try {
                await fetch(`/api/tasks/${task.id}/sprint`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ sprint_id: sprint.id })
                });
                await this.loadSprints();
                await this.loadTasks();
            } catch (error) {
                console.error('Error moving task:', error);
            }
        },
        toggleSprintCollapse(sprintId) {
            const index = this.collapsedSprints.indexOf(sprintId);
            if (index > -1) {
                this.collapsedSprints.splice(index, 1);
            } else {
                this.collapsedSprints.push(sprintId);
            }
        },
        openCreateSprintModal() {
            this.showCreateSprintModal = true;
        },
        closeCreateSprintModal() {
            this.showCreateSprintModal = false;
            this.newSprint = { name: '', goal: '', starts_at: '', ends_at: '' };
        },
        async createSprint() {
            try {
                const response = await fetch(`/api/projects/${this.project.id}/sprints`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.newSprint)
                });
                if (response.ok) {
                    this.closeCreateSprintModal();
                    await this.loadSprints();
                }
            } catch (error) {
                console.error('Error creating sprint:', error);
            }
        },
        async startSprint(sprint) {
            try {
                await fetch(`/api/sprints/${sprint.id}/start`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                await this.loadSprints();
            } catch (error) {
                console.error('Error starting sprint:', error);
            }
        },
        async completeSprint(sprint) {
            try {
                await fetch(`/api/sprints/${sprint.id}/close`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                await this.loadSprints();
            } catch (error) {
                console.error('Error completing sprint:', error);
            }
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('vi-VN');
        }
    }
};
</script>

<style scoped>
.backlog-container {
    padding: 20px;
}

.jira-workspace {
    background: #fff;
    border-radius: 4px;
}

.jira-workspace-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eaeaea;
}

.jira-view-links {
    display: flex;
    gap: 10px;
    align-items: center;
}

.jira-view-links a,
.jira-view-links .btn {
    padding: 8px 12px;
    border-radius: 4px;
    background: #f0f0f0;
    text-decoration: none;
    color: #333;
    font-size: 13px;
    border: none;
    cursor: pointer;
}

.jira-view-links a.active,
.jira-view-links a:hover {
    background: #0052cc;
    color: white;
}

.backlog-view {
    padding: 20px;
}

.backlog-section {
    margin-bottom: 30px;
    border: 1px solid #eaeaea;
    border-radius: 4px;
    overflow: hidden;
}

.backlog-section-title {
    background: #f5f5f5;
    padding: 12px 16px;
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sprint-header {
    background: #f5f5f5;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    user-select: none;
    justify-content: space-between;
}

.sprint-header:hover {
    background: #eee;
}

.sprint-title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sprint-meta {
    display: flex;
    gap: 15px;
    font-size: 12px;
    color: #666;
}

.sprint-status {
    padding: 2px 8px;
    border-radius: 3px;
    font-weight: 500;
    font-size: 11px;
    text-transform: uppercase;
}

.sprint-status.status-draft {
    background: #f0f0f0;
    color: #666;
}

.sprint-status.status-active {
    background: #deebff;
    color: #0052cc;
}

.sprint-status.status-completed {
    background: #dffcf0;
    color: #216e4e;
}

.sprint-actions {
    display: flex;
    gap: 8px;
}

.badge {
    background: #0052cc;
    color: white;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}

.backlog-list {
    display: flex;
    flex-direction: column;
    gap: 1px;
    background: #edf1f7;
    padding: 1px;
    min-height: 50px;
}

.backlog-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: #fff;
    cursor: move;
    user-select: none;
}

.backlog-item:hover {
    background: #f5f5f5;
}

.backlog-item-handle {
    color: #999;
    cursor: grab;
    font-size: 14px;
}

.backlog-item-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.task-key {
    font-size: 11px;
    color: #999;
    font-weight: 600;
    text-transform: uppercase;
}

.task-title {
    color: #0052cc;
    text-decoration: none;
    font-size: 13px;
    word-break: break-word;
}

.task-title:hover {
    text-decoration: underline;
}

.task-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.task-assignee img {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.task-story-points {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
}

.task-type {
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.type-task { background: #deebff; color: #0052cc; }
.type-story { background: #dffcf0; color: #216e4e; }
.type-bug { background: #ffeceb; color: #c01f1f; }
.type-epic { background: #f3f0ff; color: #5e4db2; }

.empty-state {
    padding: 20px;
    text-align: center;
    color: #999;
    font-size: 13px;
    background: #fafafa;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 4px;
    box-shadow: 0 4px 11px rgba(9, 30, 66, 0.13);
    max-width: 500px;
    width: 90%;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eaeaea;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
}

.btn-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #eaeaea;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
    font-size: 13px;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

.form-control:focus {
    outline: none;
    border-color: #0052cc;
    box-shadow: 0 0 0 2px rgba(0, 82, 204, 0.1);
}

.btn {
    padding: 6px 12px;
    border-radius: 4px;
    border: none;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
}

.btn-primary {
    background: #0052cc;
    color: white;
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
}

.btn-primary:hover {
    background: #0747a6;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

.btn-xs {
    padding: 2px 6px;
    font-size: 11px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
</style>
