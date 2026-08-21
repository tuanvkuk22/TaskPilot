import { createApp } from 'vue';
import ProjectDashboard from './components/ProjectDashboard.vue';

const app = createApp(ProjectDashboard, {
    project: window.projectDashboardData?.project || {},
    tasks: window.projectDashboardData?.tasks || [],
    comments: window.projectDashboardData?.comments || [],
});

app.mount('#project-dashboard-app');
