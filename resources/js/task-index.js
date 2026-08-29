import { createApp } from 'vue';
import '../css/task-shared.css';
import TaskList from './components/tasks/TaskList.vue';

const d = window.taskListData || {};

const app = createApp(TaskList, {
    tasks: d.tasks || [],
    projects: d.projects || [],
    stats: d.stats || { total: 0, in_progress: 0, done: 0, high_priority: 0 },
    createTaskUrl: d.create_task_url || '/tasks/create',
    selectedProject: d.selected_project || '',
    selectedStatus: d.selected_status || '',
    selectedPriority: d.selected_priority || '',
});

app.mount('#task-index-app');
