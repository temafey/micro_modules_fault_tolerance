FROM php:8.3-cli

RUN apt-get update && apt-get install -y git unzip

# amqp
RUN apt-get install -y librabbitmq-dev
RUN pecl install amqp
RUN docker-php-ext-enable amqp

# redis
RUN pecl install redis
RUN docker-php-ext-enable redis

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_MEMORY_LIMIT -1

RUN mkdir /.composer_cache
ENV COMPOSER_CACHE_DIR /.composer_cache

RUN mkdir /packages
COPY packages /packages/
WORKDIR /packages/FaultTolerance

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer -vvv global require hirak/prestissimo
RUN composer install
