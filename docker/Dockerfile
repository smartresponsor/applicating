# syntax=docker/dockerfile:1
FROM php:8.4-cli

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --classmap-authoritative

COPY bin/ bin/
COPY config/ config/
COPY public/ public/
COPY src/ src/
COPY templates/ templates/
COPY .env ./
COPY .env.docker ./.env.docker

RUN composer dump-autoload --no-dev --classmap-authoritative

ENV APP_ENV=prod \
    APP_DEBUG=0

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/router.php"]
