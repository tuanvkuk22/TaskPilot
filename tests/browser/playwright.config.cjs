const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
    testDir: '.',
    testMatch: '*.spec.cjs',
    timeout: 30000,
    expect: { timeout: 10000 },
    retries: 1,
    workers: 1,
    reporter: [['list'], ['html', { open: 'never', outputFolder: 'playwright-report' }]],
    outputDir: 'test-results',
    use: {
        baseURL: process.env.CI_BASE_URL || 'http://127.0.0.1:8000',
        browserName: 'chromium',
        launchOptions: { executablePath: process.env.CI_CHROMIUM_PATH || undefined },
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
});
