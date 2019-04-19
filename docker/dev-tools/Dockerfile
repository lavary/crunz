FROM php:7.3-cli-alpine

RUN apk add --no-cache \
        shadow \
        su-exec && \
    usermod --non-unique --uid 1000 www-data && \
    apk del shadow && \
    docker-php-ext-install -j$(nproc) \
        opcache

RUN mkdir -p \
        /var/log/php \
        /var/www/.composer \
    && touch /var/log/php/error.log \
    && chown www-data:www-data \
        /var/log/php/error.log \
        /var/www/.composer

COPY --from=composer:1.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME /var/www/.composer
RUN su-exec www-data composer global require hirak/prestissimo -a && rm -rf /var/www/.composer/cache
