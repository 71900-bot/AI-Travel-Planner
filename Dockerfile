FROM php:8.3-cli

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    git unzip curl libsqlite3-dev nodejs npm

# PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# ========================
# Laravel backend install
# ========================
RUN composer install --no-dev --optimize-autoloader

# ========================
# SQLite fix (你之前500原因)
# ========================
RUN mkdir -p database \
    && touch database/database.sqlite

# ========================
# Vite build (你现在500原因)
# ========================
RUN npm install \
    && npm run build

# ========================
# Laravel optimization
# ========================
RUN php artisan config:clear \
    && php artisan cache:clear

EXPOSE 10000

CMD php -S 0.0.0.0:$PORT -t public
