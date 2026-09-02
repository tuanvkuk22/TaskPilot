import { createApp } from 'vue';
import '../css/task-shared.css';
import ProjectDashboard from './components/projects/ProjectDashboard.vue';

const app = createApp(ProjectDashboard, {
    project: window.projectDashboardData?.project || {},
    tasks: window.projectDashboardData?.tasks || [],
    comments: window.projectDashboardData?.comments || [],
    activities: window.projectDashboardData?.activities || [],
});

app.mount('#project-dashboard-app');
