# Admin Auth & Roles (v20.5)
*updated 2025-10-08*

## Поток авторизации
- `/api/admin/auth/login` → возвращает JWT (демо: base64 payload) с ролью `ROLE_SUPERADMIN` или `ROLE_TENANT_OWNER`.
- UI хранит токен в `localStorage` и добавляет `Authorization: Bearer` к запросам.

## Доступы
- Конфиг `config/packages/admin_access.yaml` — маппинг endpoint → разрешённые роли.
- Middleware `AdminAccessVoter` применяет правила.

## Интеграции
- Stripe: `/api/admin/billing/stripe/:tenantId` берёт customer/subscription из таблицы `subscriptions`.
- Usage: `UsageController::current()` читает `monthly_usage` за текущий месяц.
- Incidents: `AdminIncidentController::list()` читает `ai_incidents`.

## TODO для продакшена
- Заменить демо-JWT на `lexik/jwt-authentication-bundle`.
- Валидировать Stripe webhook signature.
- Добавить rate limit, CSRF защиту для форм логина.
