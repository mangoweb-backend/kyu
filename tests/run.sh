#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="$(dirname "$DIR")"

"$ROOT_DIR/vendor/bin/tester" \
	-p php \
	-d extension="/usr/local/opt/php70-redis/redis.so" \
	"$ROOT_DIR/tests/cases/"
