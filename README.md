# Applicating / Application

Symfony-oriented application lifecycle component centered on `Application` as a software ecosystem unit, not a retail product.

## Scope

- application catalog and listing metadata
- manifest management and release/version publication
- tenant assignment, installation and enable/disable lifecycle
- runtime, sandbox, billing and governance hooks
- admin UI, JSON admin API and Symfony Console operations
- diagnostics, reports and demo fixtures

Retail product CRM, goods catalog and legacy `Product` semantics from earlier iterations were moved out of the active runtime into `legacy/` so they no longer define container wiring, routes, tests or UI vocabulary.

## Runtime

1. Install dependencies:
   ```bash
   composer install
   ```
2. Create schema:
   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```
3. Load demo data:
   ```bash
   php bin/console applicating:fixtures:load-demo
   ```
4. Start the app:
   ```bash
   symfony server:start -d
   ```
   Fallback:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

Login page: `/login`

Demo users:

- `admin / admin`
- `manager / manager`
- `viewer / viewer`

## Main flows

- `/admin/applications` for application listing and management
- `/admin/applications/{id}` for releases, manifests and tenant assignments
- `/api/admin/applications` for admin JSON vocabulary centered on `Application`
- `php bin/console applicating:*` for operational workflows

## Quality gate

Primary local pipeline:

```bash
composer pipeline:local:full
```

Reports are written to `var/reports/`.
