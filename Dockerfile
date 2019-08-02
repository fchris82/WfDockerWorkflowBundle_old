FROM php:7.3-alpine

# We need docker and xdebug!
RUN apk --no-cache add docker-cli $PHPIZE_DEPS && \
    pecl install xdebug && docker-php-ext-enable xdebug && \
    apk del --purge $PHPIZE_DEPS
