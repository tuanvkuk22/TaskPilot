import { createApp } from 'vue';
import '../css/home.css';
import HomeDashboard from './components/home/HomeDashboard.vue';

const data = window.homeData || {};

const app = createApp(HomeDashboard, {
    userName: data.user_name || '',
    totalProjects: Number(data.total_projects || 0),
    totalTasks: Number(data.total_tasks || 0),
    totalCompanies: Number(data.total_companies || 0),
    userTasksCount: Number(data.user_tasks || 0),
    unassignedTasks: data.unassigned_tasks || [],
    routes: data.routes || {}
});

app.mount('#home-app');
