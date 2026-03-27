# Smartresponsor Product Suite v24.x (Meta Bundle)

## Версии и структура
| Версия | Назначение |
|--------|-------------|
| **v24.0 Federated GraphQL Gateway** | Шина данных и субграфов |
| **v24.1 Billing & Usage Engine** | Балансы, лимиты, Stripe/PayPal/Crypto |
| **v24.2 Plugin Marketplace** | Каталог и установка плагинов |
| **v24.3 Developer Hub & SDK** | Инструменты и API для разработчиков |
| **v24.4 Distributed Deployment** | K8s / Envoy Mesh |
| **v24.5 Governance Layer** | ACL, Trust Index, DAO-управление |

## Общая схема
```
[Gateway] → [Billing] → [Marketplace] → [DeveloperHub]
                  ↓                 ↑
             [Governance] ← [Deployment]
```

## Как использовать
1. Распакуйте нужную версию.
2. Импортируйте SQL (`migrations/sql/*.sql`).
3. Настройте переменные окружения `.env`.
4. Для CI/CD используйте GitHub Workflows из `.github/workflows`.


Собрано: 2025-10-09
