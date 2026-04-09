PHP ?= php
CONSOLE = $(PHP) bin/console

install:
	composer install

migrate:
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

fixtures:
	$(CONSOLE) applicating:fixtures:load-demo --no-interaction

serve:
	$(PHP) -S 127.0.0.1:8000 -t public

qa-style:
	composer qa:style

qa-static:
	composer qa:static

qa-smell:
	composer qa:smell

qa-test:
	composer test

qa-full:
	composer pipeline:local:full
