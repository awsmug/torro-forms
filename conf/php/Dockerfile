FROM php:7.2-fpm

RUN apt-get update \
    && apt-get install -y \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    && yes | pecl install xdebug \
    && docker-php-ext-configure gd \
		--enable-gd-native-ttf \
		--with-freetype-dir=/usr/include/freetype2 \
		--with-png-dir=/usr/include \
		--with-jpeg-dir=/usr/include \
    && docker-php-ext-install gd \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

RUN curl --location --output /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 && \
    chmod +x /usr/local/bin/mhsendmail

RUN echo 'sendmail_path="/usr/local/bin/mhsendmail --smtp-addr=mailcatcher:1025 --from=no-reply@docker.dev"' > /usr/local/etc/php/conf.d/mailhog.ini

