# Legal Contracts & Assurance Mesh (v41.0)
Обновлено: 2025-10-09

## Что делает
- Шаблонизирует DPA/SLA на основе фактов Compliance Nexus.
- Проверяет соответствие системных параметров условиям контрактов.
- Пишет результаты в базу (и далее — в System Audit / Transparency).

## Эндпоинты
- POST `/api/legal/template` — рендер любого шаблона
- POST `/api/legal/dpa` — быстрый DPA из seed-шаблона
- POST `/api/legal/assurance/check` — валидация условий (minRetention, maxDpia)

## Деплой
1) Применить `066_legal_assurance.sql`.
2) Выполнить `bin/legal-seed-templates <psql-uri>`.
3) Подключить CronJob `smartpolicy-legal-assurance-verify`.
