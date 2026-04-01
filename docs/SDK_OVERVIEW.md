# Developer Surface & SDK Overview (v26.4)
*обновлено 2026-04-01*

## Текущий developer surface
В текущем Applicating-репозитории developer surface сосредоточен не вокруг отдельного plugin-SDK, а вокруг Symfony-oriented application lifecycle runtime.

## Что реально есть
- Admin UI для управления application aggregate.
- JSON API для admin inventory и report summary:
  - `GET /api/admin/applications`
  - `GET /api/admin/applications/report`
- CLI publish entrypoint:
```bash
php bin/console applicating:application:publish <application-slug>
```

## Что важно для разработчика
- `Application` — центральная сущность.
- Release и manifest публикуются и валидируются через lifecycle service.
- Publish eligibility вынесен в отдельный service contract:
  - `ApplicationPublishEligibilityServiceInterface`
  - `ApplicationPublishEligibilityService`
- Container wiring и integration coverage уже фиксируют этот contract.

## Чего здесь сейчас нет как канона
- Нет подтверждённого `sr-dev init my-plugin` / `sr-dev publish my-plugin` developer workflow.
- Нет подтверждённого `/api/devhub/plugin/*` operational surface как основы текущего runtime.
- Нет основания описывать этот репозиторий как plugin marketplace SDK.

## Дальше
- Документировать только те developer entrypoints, которые реально существуют в code/runtime.
- При появлении отдельного SDK слоя описывать его отдельно от application workspace.
