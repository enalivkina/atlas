#CLI
FROM php:8.5-cli-alpine3.24 AS cli

ARG USER='www-data'
ARG GROUP='www-data'

ENV COMPOSER_MEMORY_LIMIT='-1'

COPY --from=mlocati/php-extension-installer:2.11.12 /usr/bin/install-php-extensions /usr/local/bin/

RUN pear update-channels && pecl update-channels && install-php-extensions @composer-2 \
    pdo_pgsql pgsql intl gd opcache zip rdkafka redis

COPY ./.docker/php/php.ini-production $PHP_INI_DIR/php.ini

WORKDIR /app

COPY ./ ./

RUN composer install --verbose --no-ansi --no-interaction \
    --no-cache --no-progress --optimize-autoloader
