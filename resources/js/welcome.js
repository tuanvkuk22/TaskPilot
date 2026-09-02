import { createApp } from 'vue';
import '../css/welcome.css';
import WelcomePage from './components/WelcomePage.vue';

const d = window.welcomeData || {};

const app = createApp(WelcomePage, {
    isAuthenticated: d.is_authenticated || false,
    userName: d.user_name || '',
    homeUrl: d.home_url || '/home',
    loginUrl: d.login_url || '/login',
    registerUrl: d.register_url || '/register',
    welcomeUrl: d.welcome_url || '/',
});

app.mount('#welcome-app');
