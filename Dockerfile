FROM dunglas/frankenphp@sha256:b0a33772cdf589743f8105f12db3314616a39075462da627b7941edaf11afab1

WORKDIR /app

# PHP extensions required by Laravel
RUN install-php-extensions \
    mbstring \
    xml \
    ctype \
    fileinfo \
    openssl \
    zip \
    bcmath \
    pcntl \
    opcache

COPY --from=composer@sha256:dc292c5c0f95f526b051d4c341bf08e7e2b18504c74625e3203d7f123050e318 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --optimize-autoloader

COPY Caddyfile /etc/frankenphp/Caddyfile
COPY . .

RUN php artisan storage:link --no-interaction || true

EXPOSE 80
