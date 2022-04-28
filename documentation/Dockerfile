FROM php:7.4-fpm

# Set TIMEZONE
RUN rm /etc/localtime \
    && ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime \
    && "date"

# Install TOOLS
RUN apt-get update \
    && apt-get install -y git curl wget unzip \
    && rm -rf /var/lib/apt/lists/*

# Install OPCACHE extension
RUN docker-php-ext-install opcache

# Install APCU extension
RUN pecl install apcu \
    && docker-php-ext-enable apcu

# Install INTL extension
RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && rm -rf /var/lib/apt/lists/*

# Install EXIF extension
RUN docker-php-ext-install exif

# Install ZIP extension
RUN apt-get update \
    && apt-get install -y libzip-dev zip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Install MYSQLI extension
RUN docker-php-ext-install mysqli

# Install PDO MYSQL extension
RUN docker-php-ext-install pdo_mysql

# Install XDEBUG extension
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Blackfire PHP Probe
RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && architecture=$(uname -m) \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/$architecture/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8307\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz


# Install COMPOSER
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer
RUN composer self-update 2.2.9

# Install SYMFONY BINARY
RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv ~/.symfony/bin/symfony /usr/local/bin/symfony

# Permission fix (cf http://blog.michaelperrin.fr/2017/04/14/docker-for-mac-on-a-symfony-app/)
RUN usermod -u 1000 www-data