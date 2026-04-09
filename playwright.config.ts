import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/Playwright',
  timeout: 30_000,
  fullyParallel: true,
  retries: 0,
  reporter: 'list',
  webServer: {
    command: 'php -S 127.0.0.1:8000 -t public',
    url: 'http://127.0.0.1:8000/login',
    reuseExistingServer: true,
    timeout: 30_000,
  },
  use: {
    trace: 'on-first-retry',
    headless: true,
    baseURL: 'http://127.0.0.1:8000',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
