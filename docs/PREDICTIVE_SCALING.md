# Predictive Scaling (v21.1)
*updated 2025-10-08*

## Алгоритм
- Скользящее среднее последних 5 точек.
- Линейная регрессия для оценки тренда (slope).
- Решение:
  - `scale-up` если predicted ≥ 75% или avg ≥ 70% и рост.
  - `scale-down` если predicted ≤ 25% и отрицательный тренд.
  - иначе `no-change`.

## Пример
```bash
bin/predictive-scale-run 40,45,55,60,70
```

## Интеграция
- Снимайте метрики CPU/latency из Prometheus, превращайте в ряд 0..100.
- Передавайте в движок и применяйте результат в HPA/Helm values.
