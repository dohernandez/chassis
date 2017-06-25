FROM php:7.1-fpm-alpine

# Install packages
RUN apk add \
    supervisor \
    nginx \
    postgresql-client \
    postgresql-dev \
    php7-redis \
    --repository=http://dl-cdn.alpinelinux.org/alpine/edge/community \
    --no-cache \
    --update

# Install PHP extensions
RUN docker-php-ext-install bcmath pdo_pgsql opcache

# Enable redis
RUN mv /usr/lib/php7/modules/redis.so /usr/local/lib/php/extensions/no-debug-non-zts-20160303 && \
    echo 'extension=redis.so' > /usr/local/etc/php/conf.d/redis.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Create folders
RUN mkdir -p \
    /run/supervisord /var/log/supervisord \
    /run/nginx \
    /var/log/chassis
RUN chown www-data /var/log/chassis

# Replace configuration files
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

WORKDIR /server/http

# Start supervisord
ENTRYPOINT ["/usr/bin/supervisord"]
CMD ["-c", "/etc/supervisor/conf.d/supervisord.conf"]
