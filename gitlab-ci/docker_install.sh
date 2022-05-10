# Install Docker container dependencies
apt-get update -yqq
apt-get install git wget zip unzip libzip-dev libxml2-dev libpng-dev -yqq

# PHP extensions
docker-php-ext-install zip soap gd exif

# Install Composer dependencies
wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php'); unlink('installer.sig');"
php composer.phar install
mv composer.phar /usr/local/bin/composer
