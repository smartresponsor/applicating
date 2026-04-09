# Applicating / Application — INSTALL

## Requirements
- PHP 8.4+
- SQLite for local demo or PostgreSQL for shared environments
- Composer
- optional: Symfony CLI, Docker, kubectl, Helm

## 1) Install dependencies
```bash
composer install
```

## 2) Configure environment
The active runtime uses the standard Symfony `App\\` namespace and a persistent Applicating security profile backed by Doctrine users.

Demo credentials become available after loading fixtures:
- `admin / admin`
- `manager / manager`
- `viewer / viewer`

## 3) Create database schema
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

## 4) Load demo fixtures
```bash
php bin/console applicating:fixtures:load-demo
```

## 5) Start the application
```bash
symfony server:start -d
```
Fallback:
```bash
php -S 127.0.0.1:8000 -t public
```

## 6) Verify runtime
- `GET /health`
- `GET /ready`
- `GET /login`
- `GET /admin/applications`

## 7) Local quality gate
```bash
composer pipeline:local:full
```

## Deployment contour
The active deployment chart is `helm/applicating`, and the related GitHub CD workflow only reacts to Applicating chart changes.
