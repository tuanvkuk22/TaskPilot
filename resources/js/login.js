import { createApp } from 'vue';
import '../css/login.css';
import LoginForm from './components/auth/LoginForm.vue';

const d = window.loginData || {};

const app = createApp(LoginForm, {
    csrfToken: d.csrf_token || '',
    errors: d.errors || [],
    old: d.old || {},
    loginUrl: d.login_url || '/login',
    googleRedirectUrl: d.google_redirect_url || '',
    passwordResetUrl: d.password_reset_url || '',
    registerUrl: d.register_url || '/register',
    statusMessage: d.status || '',
});

app.mount('#login-app');
