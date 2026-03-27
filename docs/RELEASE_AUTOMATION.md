# Release Automation & Notifications (v18.5)

## Slack Integration
```yaml
slack:
  webhook_url: https://hooks.slack.com/services/XXX/YYY/ZZZ
  default_emoji: ":rocket:"
```
PHP:
```php
$notifier = new \App\Component\Product\Integration\Notifier\SlackNotifier($url);
$notifier->send('Release v18.5 deployed to prod!');
```

## Telegram Integration
```yaml
telegram:
  bot_token: "YOUR_BOT_TOKEN"
  chat_id: "YOUR_CHAT_ID"
```
PHP:
```php
$t = new \App\Component\Product\Integration\Notifier\TelegramNotifier($token, $chat);
$t->send('New release deployed: v18.5');
```

## CI/CD Integration
- `.github/workflows/release-notify.yml` — автоматическое уведомление о релизе.
- `.github/workflows/smoke-postdeploy.yml` — smoke-тест после деплоя.

## Makefile
```
make release-notify
make smoke
```
