import { createApp } from 'vue';
import '../css/edit-shared.css';
import '../css/task-shared.css';
import TaskCreate from './components/tasks/TaskCreate.vue';

const d = window.taskCreateData || {};

const app = createApp(TaskCreate, {
    projects: d.projects || [],
    users: d.users || [],
    reporters: d.reporters || [],
    epics: d.epics || [],
    sprints: d.sprints || [],
    availableLabels: d.available_labels || [],
    csrfToken: d.csrf_token || '',
    storeUrl: d.store_url || '/tasks',
    formDataUrl: d.form_data_url || '/tasks/form-data',
    recommendUrl: d.recommend_url || '/tasks/recommend-assignee',
    tasksIndexUrl: d.tasks_index_url || '/tasks',
    oldLabels: d.old_labels || '',
    old: d.old || {},
    errors: d.errors || {},
    projectId: d.project_id || null,
    defaultStatus: d.default_status || null,
    activeStatuses: d.active_statuses || [],
    effectiveWorkflow: d.effective_workflow || null,
    task: d.task || null,
    isEditing: Boolean(d.is_edit),
});

app.mount('#task-create-app');
