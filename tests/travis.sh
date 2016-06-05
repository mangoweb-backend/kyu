#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="$(dirname "$DIR")"

"$ROOT_DIR/vendor/bin/tester" \
	-p php \
	-c ~/.phpenv/versions/"$(phpenv version-name)"/etc/php.ini \
	-d extension=/home/travis/build/mangoweb-backend/kyu/phpredis-php7/modules/redis.so \
	-o junit \
	--coverage "$ROOT_DIR/coverage.xml" \
	--coverage-src "$ROOT_DIR/src" \
	tests/cases/
