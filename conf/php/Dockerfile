FROM php:fpm

RUN apt-get update \
    && apt-get install -y \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    && docker-php-ext-install gd \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

RUN curl --location --output /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 && \
    chmod +x /usr/local/bin/mhsendmail

RUN echo 'sendmail_path="/usr/local/bin/mhsendmail --smtp-addr=mailcatcher:1025 --from=no-reply@docker.dev"' > /usr/local/etc/php/conf.d/mailhog.ini

