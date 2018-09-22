#!/usr/bin/env bash

export DEBIAN_FRONTEND=noninteractive;

PHP_VERSION="$1";

add-apt-repository -y -u ppa:ondrej/php;

apt-get install git \
    unzip \
    make \
    "php$PHP_VERSION" \
    "php$PHP_VERSION-cli" \
    "php$PHP_VERSION-curl" \
    "php$PHP_VERSION-common" \
    "php$PHP_VERSION-zip" \
    "php$PHP_VERSION-mbstring" \
    "php$PHP_VERSION-opcache" \
    "php$PHP_VERSION-xml" \
    "php$PHP_VERSION-intl" -y

PHP_INI_PATH="/etc/php/$PHP_VERSION/cli/conf.d/99-user.ini";

touch /var/log/php_error.log
chown vagrant:vagrant /var/log/php_error.log

touch "$PHP_INI_PATH";

echo "display_errors = On" >> "$PHP_INI_PATH";
echo "display_startup_errors = On" >> "$PHP_INI_PATH";
echo "error_reporting = E_ALL" >> "$PHP_INI_PATH";
echo "log_errors = On" >> "$PHP_INI_PATH";
echo "error_log = /var/log/php_error.log" >> "$PHP_INI_PATH";
