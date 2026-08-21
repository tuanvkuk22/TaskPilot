import { createApp } from 'vue';
import ProjectRoles from './components/ProjectRoles.vue';

console.log('project-roles.js loading...');
console.log('projectRolesData:', window.projectRolesData);

window.addEventListener('error', function(event) {
    console.error('Global error:', event.error);
    const mountEl = document.getElementById('project-roles-app');
    if (mountEl) {
        mountEl.innerHTML = '<div style="padding:20px;color:red;">Lỗi: ' + (event.error?.message || 'Không xác định') + '</div>';
    }
});

window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled rejection:', event.reason);
    const mountEl = document.getElementById('project-roles-app');
    if (mountEl) {
        mountEl.innerHTML = '<div style="padding:20px;color:red;">Lỗi Promise: ' + (event.reason?.message || event.reason) + '</div>';
    }
});

try {
    const app = createApp(ProjectRoles, {
        project: window.projectRolesData?.project || {},
        roles: window.projectRolesData?.roles || [],
        csrfToken: window.projectRolesData?.csrf_token || '',
    });

    const mountEl = document.getElementById('project-roles-app');
    console.log('Mount element:', mountEl);

    if (mountEl) {
        app.mount('#project-roles-app');
        console.log('project-roles.js mounted successfully');
    } else {
        console.error('Mount element #project-roles-app not found');
    }
} catch (error) {
    console.error('Failed to initialize project-roles app:', error);
    const mountEl = document.getElementById('project-roles-app');
    if (mountEl) {
        mountEl.innerHTML = '<div style="padding:20px;color:red;">Lỗi khởi tạo: ' + error.message + '</div>';
    }
}
