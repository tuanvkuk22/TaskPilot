import { createApp } from 'vue';
import '../css/task-shared.css';
import ProjectDashboard from './components/projects/ProjectDashboard.vue';

const data = window.projectDashboardData || {};

const app = createApp(ProjectDashboard, {
    project: data.project || {},
    tasks: data.tasks || [],
    comments: data.comments || [],
    activities: data.activities || [],
    effectiveWorkflow: data.effective_workflow || data.project?.effective_workflow || null,
    csrfToken: data.csrf_token || '',
    updateUrl: data.update_url || '',
    editUrl: data.edit_url || `/projects/${data.project?.id || ''}/edit`,
});

app.mount('#project-dashboard-app');
