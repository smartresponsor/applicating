import { test, expect } from '@playwright/test';

test('login page exposes application-oriented management copy', async ({ page }) => {
  await page.goto('/login');
  await expect(page.locator('h1')).toContainText('Application Workspace Login');
  await expect(page.locator('body')).toContainText('Applicating');
});
