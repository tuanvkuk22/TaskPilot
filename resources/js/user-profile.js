import { createApp } from 'vue';
import UserProfile from './components/profile/UserProfile.vue';

const appData = window.profileData || {};

const app = createApp(UserProfile, {
    user: appData.user || {},
    stats: appData.stats || {},
    initialSkills: appData.initialSkills || [],
    matchingTasks: appData.matchingTasks || [],
});

app.mount('#user-profile-app');
