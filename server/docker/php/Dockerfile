FROM php:8.4-fpm

# Install requirement
RUN set -xe; \
    apt-get update; \
    apt-get install -y \
        bash \
        bash-completion \
        curl \
        openssl \
        git \
        make \
        grep \
        jq \
        unzip \
        wget \
        nano \
        zlib1g-dev \
        libzip-dev \
        libicu-dev \
        librabbitmq-dev \
        graphviz \
    ;

WORKDIR /var/www/html

RUN set -xe; \
    curl -sl https://getcomposer.org/composer-stable.phar -o /usr/local/bin/composer; \
    chmod +x /usr/local/bin/composer; \
    curl -Ls https://github.com/fabpot/local-php-security-checker/releases/download/v1.0.0/local-php-security-checker_1.0.0_linux_amd64 -o /usr/local/bin/local-php-security-checker; \
    chmod +x /usr/local/bin/local-php-security-checker;

RUN set -xe; \
    docker-php-ext-install -j$(nproc) \
        opcache \
        pdo_mysql \
        zip \
        intl \
        bcmath \
    ;

# AMQP
RUN pecl install amqp
RUN docker-php-ext-enable amqp

# For Sentry
RUN pecl install excimer
RUN docker-php-ext-enable excimer

RUN cd /usr/local/etc/php/conf.d/ && \
  echo 'memory_limit = 1G' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
  echo 'upload_max_filesize = 256m' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
  echo 'post_max_size = 256m' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# Xdebug
RUN pecl install xdebug
COPY configuration/xdebug.ini /usr/local/etc/php/conf.d
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" >> /usr/local/etc/php/conf.d/xdebug.ini;

RUN touch /var/log/xdebug_remote.log
RUN chmod 777 /var/log/xdebug_remote.log

# php-fpm config
ENV PHP_FPM_PM_LOG_LEVEL=notice \
    TIMEZONE=UTC

# Final configuration
ARG USER_UID
ARG USER_GID

RUN set -xe; \
    mkdir -p /var/www/html; \
    chown -R ${USER_UID}:${USER_UID} /var/www;
