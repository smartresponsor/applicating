SHELL := /bin/bash
up:
	docker compose -f docker/docker-compose.yml up -d

down:
	docker compose -f docker/docker-compose.yml down -v

bootstrap:
	make up && bash scripts/migrate.sh

qa:
	vendor/bin/phpstan analyse && vendor/bin/ecs check --fix && vendor/bin/phpunit --coverage-text --min=90
