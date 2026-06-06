# Application Workspace Overview (v26.4)
*обновлено 2026-04-01*

## Что есть сейчас
- Управление `Application` как основной сущностью workspace.
- Привязка `ApplicationRelease`, `ApplicationManifest` и `TenantApplication`.
- Admin UI для создания, редактирования, публикации, suspension и tenant assignment.
- Publish guards: публикация требует manifest и как минимум один `approved` governance state.
- Eligibility UX: в admin view заранее показывается, можно ли публиковать release.

## Основные сущности
- `Application`
- `ApplicationRelease`
- `ApplicationManifest`
- `TenantApplication`

## Admin / API
- `GET /admin/applications`
- `GET /admin/applications/{id}`
- `POST /admin/applications/{id}/publish/{releaseId}`
- `GET /api/admin/application`
- `GET /api/admin/application/report`

## CLI
```bash
php bin/console applicating:application:publish <application-slug>
```

## Что это описывает
- Workspace вокруг application lifecycle и tenant/runtime management.
- Governance вокруг release + manifest, а не витрину retail-плагинов.
- Реальный operational surface текущего репозитория.

## Дальше
- Расширять readiness / governance / diagnostics вокруг application lifecycle.
- Держать docs синхронизированными с admin/API/CLI surface, а не с ранними marketplace-идеями.
