# Governance Layer (v24.5)
*обновлено 2025-10-09*

## Что решает
- Политики на уровне арендатора/глобально (запреты/разрешения для Marketplace/Plugins).
- Роли и доступ (admin/editor/viewer/dev).
- Trust Index плагинов (рейтинги, влияние на выдачу).
- Голосования/предложения: минимальное DAO-поведение (open/close, кворум — за рамками демо).
- Применение правил (enforcement) с аудитом.

## Таблицы
- gov_policies, gov_roles, gov_trust, gov_proposals, gov_votes, gov_enforcements.

## API (демо)
- POST /api/gov/policy — создать/сохранить политику
- POST /api/gov/roles/assign — выдать роль
- POST /api/gov/trust/rate — поставить оценку плагину
- POST /api/gov/proposal/create — создать предложение
- POST /api/gov/proposal/vote — проголосовать
- POST /api/gov/enforce/check — проверить действие

## Примеры правил
```json
{ "deny": ["plugin.install", "marketplace.publish"] }
```

## Интеграция
- Marketplace проверяет `enforce` для действий install/publish.
- Federated Gateway добавляет `X-Role` / `X-Tenant` на основе gov_roles.
- Self-Healing может открывать proposals для спорных автодействий.
