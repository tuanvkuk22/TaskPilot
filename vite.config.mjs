import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

const __dirname = fileURLToPath(new URL('.', import.meta.url));

export default defineConfig({
    plugins: [vue()],
    root: __dirname + '/resources/js',
    resolve: {
        alias: {
            '@': __dirname + '/resources/js',
        },
    },
    build: {
        outDir: __dirname + '/public/build',
        emptyOutDir: true,
        rollupOptions: {
            input: [
                __dirname + '/resources/js/project-create.js',
                __dirname + '/resources/js/project-edit.js',
                __dirname + '/resources/js/project-roles.js',
                __dirname + '/resources/js/project-members.js',
                __dirname + '/resources/js/project-dashboard.js',
                __dirname + '/resources/js/project-list.js',
                __dirname + '/resources/js/task-create.js',
                __dirname + '/resources/js/task-index.js',
                __dirname + '/resources/js/project-timeline.js',
                __dirname + '/resources/js/ai-assignee-widget.js',
                __dirname + '/resources/css/task-shared.css',
                __dirname + '/resources/css/ai-assignee.css',
                __dirname + '/resources/js/login.js',
                __dirname + '/resources/js/welcome.js',
                __dirname + '/resources/js/home.js',
                __dirname + '/resources/js/skills-profile.js',
                __dirname + '/resources/css/home.css',
                __dirname + '/resources/css/skills-profile.css',
            ],
            output: {
                entryFileNames: 'assets/[name].js',
                chunkFileNames: 'assets/[name].js',
                assetFileNames: 'assets/[name].[ext]',
            },
        },
    },
});
