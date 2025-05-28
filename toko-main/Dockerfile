FROM php:8.1-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN if [ ! -f .env ]; then cp .env.example .env; fi

RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
