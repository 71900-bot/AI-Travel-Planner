FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libsqlite3-dev nodejs npm

RUN docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# =====================
# PHP deps
# =====================
RUN composer install --no-dev --optimize-autoloader

# =====================
# SQLite fix
# =====================
RUN mkdir -p database \
    && touch database/database.sqlite

# =====================
# Vite build
# =====================
RUN npm install \
    && npm run build

# =====================
# Laravel optimize (IMPORTANT ORDER FIX)
# =====================
RUN php artisan view:clear \
    && php artisan cache:clear \
    && php artisan config:clear \
    && php artisan route:clear

EXPOSE 10000

CMD php -S 0.0.0.0:$PORT -t public
