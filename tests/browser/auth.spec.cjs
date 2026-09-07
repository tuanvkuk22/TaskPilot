const { test, expect } = require('@playwright/test');

test.beforeAll(async ({ request }) => {
    await expect.poll(async () => {
        try {
            return (await request.get('/login')).status();
        } catch {
            return 0;
        }
    }, { timeout: 30000 }).toBe(200);
});

test('production login mounts Vue, loads assets and rejects invalid credentials', async ({ page }) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('response', response => {
        if (new URL(response.url()).pathname.startsWith('/build/') && response.status() >= 400) {
            errors.push(response.url() + ': ' + response.status());
        }
    });
    const response = await page.goto('/login');
    expect(response.status()).toBe(200);
    await expect(page.locator('#login-app form')).toBeVisible();
    await page.getByLabel('Email', { exact: true }).fill('missing@example.test');
    await page.locator('input[name="password"]').fill('invalid-password');
    await page.locator('button[type="submit"]').click();
    await expect(page.locator('.tp-login-alert-error')).toBeVisible();
    await expect(page).toHaveURL(/\/login$/);
    expect(errors).toEqual([]);
});

test('guest cannot access projects', async ({ page }) => {
    await page.goto('/projects');
    await expect(page).toHaveURL(/\/login$/);
    await expect(page.locator('#login-app form')).toBeVisible();
});

test('welcome page renders successfully', async ({ page }) => {
    const response = await page.goto('/');
    expect(response.status()).toBe(200);
    await expect(page.locator('body')).toContainText('TaskPilot');
});
