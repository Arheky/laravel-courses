# -----------------------------
# LaravelCourses - Render Deploy (PostgreSQL)
# -----------------------------

# 1️⃣ Base Image
FROM php:8.2-fpm

# 2️⃣ System dependencies (PostgreSQL dahil)
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# 3️⃣ Composer yükle
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 4️⃣ Çalışma dizini
WORKDIR /var/www/html

# 5️⃣ Tüm dosyaları kopyala
COPY . .

# 6️⃣ Laravel bağımlılıklarını yükle
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# 7️⃣ Laravel izinleri
RUN chmod -R 775 storage bootstrap/cache

# 8️⃣ ENV değişkenleri
ENV APP_ENV=production
ENV PORT=8000

# 9️⃣ Sunucu başlat (migrate dahil)
EXPOSE 8000
CMD php artisan migrate --force && php artisan optimize:clear && php artisan config:cache && php artisan serve --host=0.0.0.0 --port=8000
