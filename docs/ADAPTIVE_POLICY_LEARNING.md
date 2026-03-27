# Adaptive Policy Learning (v29.1)
*обновлено 2025-10-09*

## Идея
- Политики имеют **варианты** (A/B/C), у каждого — набор effects (скидки, лимиты, trust_bonus).
- Bandit (Thompson) распределяет трафик между вариантами и обучается по reward.

## Таблицы
- `policy_variant(policy, variant_id, effects, alpha, beta, wins, trials)`
- `policy_assignment(policy, variant, tenant_id, ts)`
- `policy_feedback(policy, variant, reward, context, ts)`

## API
- `POST /api/policy/learning/variant/define {{policy, variant, effects(json)}}`
- `POST /api/policy/learning/assign {{policy, tenant}}` → `{{variant,effects}}`
- `POST /api/policy/learning/feedback {{policy, variant, tenant, reward, context(json)}}`
- `GET  /api/policy/learning/status?policy=...`

## Интеграция
- Вызывайте `/assign` перед PolicyFlow → используйте выданные `effects` как входные эффекты.
- После бизнес-события (покупка, удержание) вызывайте `/feedback` с reward.
