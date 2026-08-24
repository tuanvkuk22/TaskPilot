import { createApp } from 'vue';
import ProjectList from './components/ProjectList.vue';

const app = createApp(ProjectList, {
  projects: window.projectListData?.projects || [],
  stats: window.projectListData?.stats || { total: 0, active: 0, completed: 0, archived: 0 },
});

app.mount('#project-list-app');
