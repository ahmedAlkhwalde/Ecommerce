FROM php:8.2-fpm

# تثبيت الحزم المطلوبة للنظام وPHP
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev pkg-config zlib1g-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd

# تحميل Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# تثبيت الحزم
RUN composer require predis/predis --ignore-platform-reqs
RUN composer install --optimize-autoloader --no-scripts --no-interaction --ignore-platform-reqs

# ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD sh -c "php artisan migrate --force && php artisan queue:work --queue=firebase,schedule_updates,shift_swap,default --tries=3 --timeout=90 & exec php artisan serve --host=0.0.0.0 --port=8000"