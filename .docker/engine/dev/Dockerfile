FROM php:8.1-fpm

# add-ons and extensions for PHP
RUN apt-get update && apt-get install -y \
        acl \
        git \
        nano \
        openssl \
        procps \
        unzip \
        wget \
        libicu-dev \
        zlib1g-dev \
        libzip-dev \
        libxml2-dev \
        --no-install-recommends && rm -r /var/lib/apt/lists/*
RUN docker-php-ext-install \
        mysqli \
        pdo \
        pdo_mysql \
        zip

# intl
RUN apt-get -y update \
        && apt-get install -y libicu-dev \
        && docker-php-ext-configure intl \
        && docker-php-ext-install intl

# apcu
RUN pecl install apcu \
        && docker-php-ext-enable apcu
RUN echo "extension=apcu.so" > /usr/local/etc/php/php.ini
RUN echo "apc.enable_cli=1" > /usr/local/etc/php/php.ini
RUN echo "apc.enable=1" > /usr/local/etc/php/php.ini

# opcache
RUN docker-php-ext-configure opcache --enable-opcache \
        && docker-php-ext-install opcache

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer self-update --2

# Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash && mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# xdebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.discover_client_host = true" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.idekey=\"PHPSTORM\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "integer xdebug.client_port" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Blackfire PHP Probe
RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
        && architecture=$(uname -m) \
        && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/$architecture/$version \
        && mkdir -p /tmp/blackfire \
        && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
        && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
        && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
        && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

# Permission fix (cf http://blog.michaelperrin.fr/2017/04/14/docker-for-mac-on-a-symfony-app/)
RUN usermod -u 1000 www-data