import { expect, test } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { join } from 'node:path';

test.describe('Category admin form UI contract (Twig + Bootstrap)', () => {
  test('template keeps bootstrap layout and actions for create/edit flow', async ({ page }) => {
    const twigPath = join(process.cwd(), 'templates/category/form.html.twig');
    const template = readFileSync(twigPath, 'utf-8');

    expect(template).toContain("form_start(form, {attr: {class: 'row g-3'}})");
    expect(template).toContain("class=\"btn btn-primary\">Save</button>");
    expect(template).toContain("class=\"btn btn-outline-secondary\">Cancel</a>");
    expect(template).toContain("is_edit ? 'Edit category' : 'Create category'");

    await page.setContent(`
      <div class="container py-4">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="card shadow-sm">
              <div class="card-header bg-light">
                <h1 class="h4 mb-0">Create category</h1>
              </div>
              <div class="card-body">
                <form class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" class="form-control" />
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="slug">Slug</label>
                    <input id="slug" class="form-control" />
                  </div>
                  <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="/admin/category" class="btn btn-outline-secondary">Cancel</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    `);

    await expect(page.getByRole('heading', { name: 'Create category' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Cancel' })).toBeVisible();
    await expect(page.locator('.row.g-3')).toHaveCount(1);
  });
});
