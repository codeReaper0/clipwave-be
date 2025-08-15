#!/bin/bash
set -e

cd /home/site/wwwroot

# Install PHP dependencies if not already installed
if [ -f composer.json ] && [ ! -d vendor ]; then
    composer install --no-dev --optimize-autoloader
fi

# Start Slim PHP server on Azure port
php -S 0.0.0.0:8000 -t . index.php
