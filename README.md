# Product CRM Core — v35.0

Минимально рабочий CRM-слой для компонента Product: пайплайн/стадии, сделки, активности, история стадий, воронка 30д. Без заглушек — все эндпоинты пишут/читают реальные таблицы.

## Установка
psql ... -f migrations/sql/088_product_crm_core.sql

## Базовый сценарий
1) Создать дефолтный пайплайн и стадии:
   ```bash
   curl -X POST http://localhost:8000/api/product/crm/pipelines/default -d 'tenant_id=demo&product_id=1'
   ```
2) Создать сделку:
   ```bash
   curl -X POST http://localhost:8000/api/product/crm/deals      -H 'Content-Type: application/json'      -d '{"tenant_id":"demo","product_id":1,"title":"Демо-сделка","amount":1000,"currency":"USD"}'
   ```
3) Перевести сделку на стадию (например, Won):
   ```bash
   curl -X POST http://localhost:8000/api/product/crm/deals/1/move -d 'to_stage_id=4&actor_user_id=10'
   ```
4) Добавить активность:
   ```bash
   curl -X POST http://localhost:8000/api/product/crm/deals/1/activities      -d 'type=note' -d 'content[body]=Позвонил, договорились'
   ```
5) Воронка:
   ```bash
   curl http://localhost:8000/api/product/crm/funnel
   ```

## Контракты/изоляция
- Сервисы под домен `Product\CRM` в `src/Product/CRM/Service/*`.
- Контроллеры изолированы в `src/Controller/Product/CrmController.php`.
- Нейминг согласован с твоими контрактами (`Product*`, доменная папка `Product`).

## OpenAPI
`config/openapi/product_crm.yaml`

