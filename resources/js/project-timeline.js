import { createApp } from 'vue';
import Timeline from './components/timelines/timeline.vue';

const app = createApp(Timeline, {
    project: window.projectTimelineData?.project || {},
    tasks: window.projectTimelineData?.tasks || [],
    sprints: window.projectTimelineData?.sprints || [],
});

app.mount('#project-timeline-app');
