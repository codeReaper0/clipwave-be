#!/bin/bash

# Configure PHP
echo "Configuring PHP..."
echo "date.timezone = UTC" >> /usr/local/etc/php/conf.d/timezone.ini

# Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm &

# Start Nginx
echo "Starting Nginx..."
nginx -g 'daemon off;'