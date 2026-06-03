FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libsqlite3-dev

RUN docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate --force

EXPOSE 10000

CMD php -S 0.0.0.0:$PORT -t public
