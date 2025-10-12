# -----------------------------
# 🚀 LaravelCourses - Multi-Stage Render Deploy (PHP 8.2 + Node 20 + PostgreSQL)
# -----------------------------

########### 1️⃣ BUILD STAGE (Node + Composer) ###########
FROM node:20-bullseye-slim AS builder

# Sistem bağımlılıkları
RUN apt-get update && apt-get install -y git unzip libpq-dev

# Çalışma dizini
WORKDIR /app

# Composer kur
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Proje dosyalarını kopyala
COPY . .

# Laravel ve frontend bağımlılıklarını yükle
RUN composer install --no-dev --optimize-autoloader \
    && npm install --legacy-peer-deps \
    && npm run build

########### 2️⃣ PRODUCTION STAGE (PHP 8.2-FPM) ###########
FROM php:8.2-fpm-alpine

# Sistem bağımlılıkları
RUN apk add --no-cache libpng-dev libjpeg-turbo-dev libwebp-dev libzip-dev libpq-dev bash \
    && docker-php-ext-install pdo pdo_pgsql bcmath mbstring zip gd

# Çalışma dizini
WORKDIR /var/www/html

# Build aşamasından gerekli dosyaları kopyala
COPY --from=builder /app/public/build ./public/build
COPY --from=builder /app/vendor ./vendor
COPY --from=builder /app/bootstrap ./bootstrap
COPY --from=builder /app/artisan ./artisan
COPY --from=builder /app/config ./config
COPY --from=builder /app/resources ./resources
COPY --from=builder /app/routes ./routes
COPY --from=builder /app/database ./database
COPY --from=builder /app/app ./app
COPY --from=builder /app/package.json ./package.json

# Laravel izinleri
RUN chmod -R 775 storage bootstrap/cache || true

# Ortam değişkenleri
ENV APP_ENV=production
ENV PORT=8000

# Port aç
EXPOSE 8000

# Başlatma komutu
CMD php artisan config:clear && php artisan cache:clear && php artisan view:clear \
    && php artisan migrate --force \
    && php artisan optimize \
    && php artisan serve --host=0.0.0.0 --port=8000
