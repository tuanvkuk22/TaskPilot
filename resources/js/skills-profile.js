import { createApp } from 'vue';
import '../css/skills-profile.css';
import SkillsProfile from './components/profile/SkillsProfile.vue';

const data = window.skillsData || {};

const app = createApp(SkillsProfile, {
    initialSkills: data.initialSkills || [],
    matchingTasks: data.matchingTasks || []
});

app.mount('#skills-profile-app');
