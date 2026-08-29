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
    tasksIndexUrl: d.tasks_index_url || '/tasks',
    oldLabels: d.old_labels || '',
    old: d.old || {},
    errors: d.errors || {},
    projectId: d.project_id || null,
});

app.mount('#task-create-app');
