# Priority Queues + Traffic Shaping (v27.3)
*обновлено 2025-10-09*

## Идея
- Очереди задач с приоритетами (0..3) — VIP рулит, но FIFO внутри приоритета.
- Traffic Shaper использует план (standard/pro/vip) + TrustScore для rate/burst.
- TokenBucket реализует ограничение потока и сглаживает пики.

## API
- `POST /api/traffic/enqueue {tenant, task, priority}`
- `POST /api/traffic/next` — получить следующее задание с учётом шейпинга.

## Интеграция
- Сочетается с SmartCloud (планы) и TrustMesh (score).
- В Gateway можно оборачивать LLM-вызовы в enqueue/next для справедливости.

## Настройка планов
- standard: rate=20 rps, burst=60
- pro: rate=50 rps, burst=120
- vip: rate=100 rps, burst=200
TrustScore добавляет до +25% к rate/burst.
