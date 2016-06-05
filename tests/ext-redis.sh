#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

# phpredis for PHP 7 is not available on PECL as of 2016-06-05

wget https://github.com/nicolasff/phpredis/archive/php7.zip
unzip php7.zip
cd phpredis-php7
phpize
./configure
make
make install

ls /home/travis/.phpenv/versions/7.0.7/lib/php/extensions/no-debug-zts-20151012/
