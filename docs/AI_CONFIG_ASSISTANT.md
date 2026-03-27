# AI Config Assistant (v21.0)
*updated 2025-10-08*

## Возможности
- Анализ Helm и Terraform конфигов на предмет неэффективных настроек.
- Генерация рекомендаций и YAML-патчей.

## Пример
```bash
php bin/ai-config-analyze charts/smartresponsor/values.yaml
php bin/ai-config-patch > patch.yaml
```

## Интеграция
Может использовать локальный OpenAI API для объяснения рекомендаций и генерации комментариев.
