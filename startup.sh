#!/bin/bash

# Use Azure's assigned port
SERVER_PORT=${PORT:-8080}

# Generate nginx config with the correct port
cat > /etc/nginx/conf.d/default.conf <<EOF
server {
    listen ${SERVER_PORT};
    # Rest of your nginx config...
}
EOF

# Start PHP-FPM (running on port 9000 - no conflict)
/usr/sbin/php-fpm

# Start Nginx (will use the port from environment variable)
exec /usr/sbin/nginx -g 'daemon off;'