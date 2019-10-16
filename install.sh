#!/usr/bin/env bash

php composer.phar install --prefer-dist  #установка вендора компосера
cp ./config/autoload/doctrine.local.php.dist ./config/autoload/doctrine.local.php
sed -i -e "s/root:12345@db\/shlk/root:12345@mysql_1\/online/g" ./config/autoload/doctrine.local.php
cp ./config/autoload/mail.local.php.test.dist ./config/autoload/mail.local.php
sed -i -e "s/TEST_DOMAIN/online\.schetmash$1\.test/g" ./config/autoload/mail.local.php
sed -i -e "s/url.*/url \= git@git\.keaz\.ru\:schetmash\/online\/app\.git/g" ./.git/config
cp ./config/autoload/local.php.dist ./config/autoload/local.php
chmod 0777 ./data/
mkdir ./data/cache/
chmod 0777 ./data/cache/
php composer.phar development-enable
