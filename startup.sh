#!/bin/bash
set -e

cd /home/site/wwwroot

# Start PHP-FPM in the background
php-fpm -D

# Start nginx in the foreground
nginx -c /home/site/wwwroot/nginx.conf -g "daemon off;"
