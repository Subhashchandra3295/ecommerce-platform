FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.3-cli-alpine AS app
RUN apk add --no-cache icu-dev oniguruma-dev libzip-dev postgresql-dev zip unzip git \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring intl bcmath

WORKDIR /app
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 4000
# Single-service simplification for a small deployment: the queue worker runs
# as a background process alongside the web server in the same container.
# A "real" production setup would run it as its own service/process manager
# so it restarts independently of the web process.
CMD ["sh", "-c", "php artisan migrate --force && (php artisan queue:work --tries=3 &) && php artisan serve --host=0.0.0.0 --port=${PORT:-4000}"]
