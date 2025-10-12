# -----------------------------
# 🚀 LaravelCourses - Render Deploy (PHP 8.2 + Node 20 + PostgreSQL)
# Güvenli Otomatik Versiyon (.env fallback dahil)
# -----------------------------

FROM php:8.2-fpm

# 1️⃣ Sistem bağımlılıkları
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# 2️⃣ Node.js kurulumu
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3️⃣ Composer kurulumu
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 4️⃣ Çalışma dizini
WORKDIR /var/www/html

# 5️⃣ Proje dosyalarını kopyala
COPY . .

# 6️⃣ Laravel + Frontend bağımlılıkları
RUN composer install --no-dev --optimize-autoloader \
    && npm install --legacy-peer-deps \
    && npm run build

# 7️⃣ Laravel izinleri
RUN chmod -R 775 storage bootstrap/cache || true

# 8️⃣ Ortam değişkenleri
ENV APP_ENV=production
ENV PORT=8000

# 9️⃣ Laravel başlangıç (env kontrolü + otomatik işlemler)
EXPOSE 8000
CMD if [ ! -f ".env" ]; then \
      echo "⚙️ .env bulunamadı, geçici oluşturuluyor..."; \
      echo "APP_KEY=base64:$(php -r 'echo base64_encode(random_bytes(32));')"; \
      echo "APP_ENV=production"; \
      echo "APP_DEBUG=false"; \
      echo "APP_URL=https://laravel-courses.onrender.com"; \
    fi \
    && php artisan config:clear \
    && php artisan cache:clear \
    && php artisan view:clear \
    && php artisan migrate --force \
    && php artisan storage:link || true \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan optimize \
    && php artisan serve --host=0.0.0.0 --port=8000
