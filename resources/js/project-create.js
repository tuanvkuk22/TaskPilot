import { createApp } from 'vue';
import '../css/edit-shared.css';
import ProjectCreate from './components/projects/ProjectCreate.vue';

const d = window.projectCreateData || {};

const app = createApp(ProjectCreate, {
    companies: d.companies || [],
    categories: d.categories || [],
    teams: d.teams || [],
    projectCodePreview: d.project_code_preview || '',
    companyId: d.company_id || null,
    errors: d.errors || {},
    old: d.old || {},
    storeUrl: d.store_url || '/projects',
    categoryStoreUrl: d.category_store_url || '',
    companyStoreUrl: d.company_store_url || '',
    csrfToken: d.csrf_token || '',
    projectsIndexUrl: d.projects_index_url || '/projects',
});

app.mount('#project-create-app');
