import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/Playwright',
  outputDir: '../var/Applicating/2026-10-04/engine-20261004111052-applicating-6fd6e9/playwright',
  timeout: 30_000,
  fullyParallel: true,
  retries: 0,
  reporter: 'list',
  webServer: {
    command: 'powershell -NoProfile -ExecutionPolicy Bypass -File bin/application-test-server.ps1 -Port 8015',
    env: {
      APP_ENV: 'test',
      APP_DEBUG: '1',
      APP_SECRET: 'applicating-test-secret',
      DATABASE_URL: 'sqlite:///%kernel.project_dir%/var/applicating_user_test.db',
      APP_DATA_DATABASE_URL: 'sqlite:///%kernel.project_dir%/var/applicating_app_data_test.db',
    },
    url: 'http://127.0.0.1:8015/login',
    reuseExistingServer: true,
    timeout: 30_000,
  },
  use: {
    trace: 'on-first-retry',
    headless: true,
    baseURL: 'http://127.0.0.1:8015',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
