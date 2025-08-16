#!/bin/bash

# Navigate to site root
cd /home/site/wwwroot

# Use Azure's assigned port
SERVER_PORT=${PORT:-8080}

# For debugging
echo "Starting server on port $SERVER_PORT" > startup.log
printenv >> startup.log

# Start PHP server (only for development/testing)
# Point to your actual front controller (index.php)
exec php -S 0.0.0.0:$SERVER_PORT -t /home/site/wwwroot /home/site/wwwroot/index.php