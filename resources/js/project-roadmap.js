import { createApp } from 'vue';
import '../css/task-shared.css';
import '../css/edit-shared.css';
import ProjectCalendar from './components/projects/ProjectCalendar.vue';

const data = window.roadmapCalendarData || {};

const app = createApp(ProjectCalendar, {
    project: data.project || {},
    effectiveWorkflow: data.effective_workflow || {},
    tasks: data.tasks || [],
    sprints: data.sprints || [],
    users: data.users || [],
});

app.mount('#roadmap-calendar-app');
