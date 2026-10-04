import { expect, test } from '@playwright/test';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';

const artifactDate = process.env.CMCP_RUN_DATE ?? new Date().toISOString().slice(0, 10);
const artifactRunId = process.env.CMCP_RUN_ID ?? 'manual';
const artifactDirectory = path.resolve('..', 'var', 'Applicating', artifactDate, artifactRunId);

test('login surface renders through Viewing', async ({ page }) => {
  // @ui-coverage surface:login
  const response = await page.goto('/login');

  expect(response).not.toBeNull();
  expect(response?.status()).toBe(200);
  await expect(page.getByRole('heading', { name: 'Interfacing interface', exact: true }).first()).toBeVisible();
  await expect(page.getByText('Root Interfacing fallback for a normalized Viewing payload.').first()).toBeVisible();
  await expect(page.getByText('Applicating', { exact: true })).toBeVisible();

  await mkdir(artifactDirectory, { recursive: true });
  await page.screenshot({
    path: path.join(artifactDirectory, 'login-surface.png'),
    fullPage: true,
  });
});
