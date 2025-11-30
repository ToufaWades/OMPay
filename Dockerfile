FROM composer:2.6 AS composer-build

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

FROM php:8.3-fpm-alpine

# Install dependencies
RUN apk add --no-cache postgresql-dev postgresql-client autoconf g++ make nginx \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Add Laravel user
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

WORKDIR /var/www/html

COPY --from=composer-build /app/vendor ./vendor
COPY . .

# Create storage & cache directories **before switching user**
RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache



# Configure nginx
COPY nginx.conf /etc/nginx/nginx.conf
RUN mkdir -p /var/cache/nginx && chown -R laravel:laravel /var/log/nginx /var/cache/nginx /run/nginx \
    && mkdir -p /tmp/nginx/client_body && chown -R laravel:laravel /tmp/nginx \
    && chown -R laravel:laravel /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configure php-fpm to run as laravel user
RUN sed -i 's/user = nobody/user = laravel/g' /etc/php8/php-fpm.d/www.conf && \
    sed -i 's/group = nobody/group = laravel/g' /etc/php8/php-fpm.d/www.conf

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
