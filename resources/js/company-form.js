import { createApp } from 'vue';
import '../css/edit-shared.css';
import CompanyForm from './components/companies/CompanyForm.vue';

const data = window.companyFormData || {};

const app = createApp(CompanyForm, {
    company: data.company || null,
    isEdit: Boolean(data.is_edit),
    actionUrl: data.action_url || '',
    backUrl: data.back_url || '',
    csrfToken: data.csrf_token || '',
    errors: data.errors || {},
    old: data.old || {},
});

app.mount('#company-form-app');
