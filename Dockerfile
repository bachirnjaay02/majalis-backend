FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpq-dev \
    && docker-php-ext-install zip pdo pdo_pgsql pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN echo "APP_NAME=MajalisStore" > .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_DEBUG=true" >> .env && \
    echo "APP_URL=https://majalis-backend.onrender.com" >> .env && \
    echo "DB_CONNECTION=pgsql" >> .env && \
    echo "LOG_CHANNEL=stderr" >> .env && \
    echo "FILESYSTEM_DISK=local" >> .env

EXPOSE 8000
CMD php artisan key:generate && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8000