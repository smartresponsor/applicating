# Applicating admin console

## UI shape
- server-rendered Symfony/Twig admin is the authoritative management surface
- experimental front-end shells are not part of the live delivery contour; archived UI experiments live under `archive/repo-drift/`

## Main screens
- `/admin/applications` — application listing
- `/admin/applications/{id}` — lifecycle detail, releases, manifests and tenant assignments

## API relation
The admin console works alongside the manager-scoped JSON API:
- `GET /api/admin/applications`
- `GET /api/admin/v1/applications`
- `GET /api/admin/applications/report`
- `GET /api/admin/v1/applications/report`

## Local UI workflow
```bash
composer install
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console applicating:fixtures:load-demo
symfony server:start -d
```

The canonical management path is the Symfony application itself. There is no live `admin-ui/` delivery surface in the active runtime contour.

## Admin proof contour
- `composer smoke:admin` validates the admin surface wiring and access-control markers.
- `composer smoke:functional-readiness` validates the broader readiness contour around admin and public entrypoints.
