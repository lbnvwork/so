#!/usr/bin/env bash

cd /var/www/html

#mkdir -m 777 data/cache

sudo -E -u www-data composer install

php-fpm7.2