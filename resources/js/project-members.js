import { createApp } from 'vue';
import ProjectMembers from './components/ProjectMembers.vue';

const app = createApp(ProjectMembers, {
    project: window.projectMembersData?.project || {},
    members: window.projectMembersData?.members || [],
    roles: window.projectMembersData?.roles || [],
    csrfToken: window.projectMembersData?.csrf_token || '',
});

app.mount('#project-members-app');
