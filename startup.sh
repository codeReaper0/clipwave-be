#!/bin/bash

# Navigate to site root
cd /home/site/wwwroot

# Use Azure's assigned port, default to 8080 if not set
PORT=${PORT:-8080}

# Start PHP built-in server with Slim's router
php -S 0.0.0.0:$PORT -t public public/index.php
