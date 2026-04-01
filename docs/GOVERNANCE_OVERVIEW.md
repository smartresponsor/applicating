# Governance Layer (v26.4)
*обновлено 2026-04-01*

## Что решает сейчас
- Определяет publish eligibility через manifest presence и `approved` governance state.
- Участвует в lifecycle-переходах `draft -> published -> suspended`.
- Даёт operator-facing причины, почему release сейчас нельзя публиковать.
- Поддерживает inspection/report contour вокруг publish guards и eligibility.

## Фактические опоры в runtime
- `ApplicationLifecycleService::publishApplication()` проверяет:
  - наличие manifest;
  - наличие хотя бы одного `approved` manifest;
  - состояние release;
  - принадлежность release текущему application.
- `ApplicationPublishEligibilityService` строит eligibility map для admin view.
- Admin/CLI surfaces не падают сырыми исключениями, а показывают readable failure reason.

## Поверхности
- Admin publish action: `POST /admin/applications/{id}/publish/{releaseId}`
- Eligibility hints: `/admin/applications/{id}`
- CLI publish: `applicating:application:publish <slug>`
- Inspection: `ApplicatingPublishGuardReport`

## Что не является текущим каноном
- Не описывает DAO, голосования или Trust Index как активный runtime этого репозитория.
- Не строится вокруг legacy `marketplace.publish` / `plugin.install` action-модели.

## Дальше
- Расширять governance только там, где есть реальный runtime contract.
- Держать policy vocabulary aligned с `application`, `release`, `manifest`, `tenant assignment`.
