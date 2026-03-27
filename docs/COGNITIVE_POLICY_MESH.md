# Cognitive Policy Mesh (v33.0)
Обновлено: 2025-10-09

## Идея
Децентрализованный консенсус для глобальных параметров/политик между регионами: предложения → голоса → кворум → применение через CRDT (LWW Map).

## API
- POST `/api/policy/mesh/propose` — создать предложение
- POST `/api/policy/mesh/vote` — голос
- POST `/api/policy/mesh/finalize` — финализация при кворуме
- GET  `/api/policy/mesh/map` — итоговая карта параметров
- GET  `/api/policy/mesh/agent/status` — состояние регионального агента

## Примеры полезных предложений
- `{"canary_percent": 25}` — обновить долю canary в rollout
- `{"predictive_threshold": 0.6}` — изменить порог предиктивного допуска
- `{"region_weight.eu": 1.2}` — скорректировать вес региона для adaptive

## Безопасность
- Голосование только от доверенных регионов (IP allowlist/mtls).
- Аудит: каждую финализацию писать в Trust Ledger.
