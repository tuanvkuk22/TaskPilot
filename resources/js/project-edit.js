import { createApp } from 'vue';
import '../css/edit-shared.css';
import ProjectEdit from './components/projects/ProjectEdit.vue';

const app = createApp(ProjectEdit, {
    project: window.projectEditData?.project || {},
    companies: window.projectEditData?.companies || [],
    categories: window.projectEditData?.categories || [],
    teams: window.projectEditData?.teams || [],
    errors: window.projectEditData?.errors || {},
    old: window.projectEditData?.old || {},
    categoryStoreUrl: window.projectEditData?.category_store_url || '',
    companyStoreUrl: window.projectEditData?.company_store_url || '',
    csrfToken: window.projectEditData?.csrf_token || '',
    updateUrl: window.projectEditData?.update_url || '',
    projectsIndexUrl: window.projectEditData?.projects_index_url || '',
    projectImageUrl: window.projectEditData?.project_image_url || '',
});

app.mount('#project-edit-app');
