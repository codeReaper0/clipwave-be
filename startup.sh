#!/bin/bash

# Find and kill any existing nginx processes
pkill nginx || true
pkill php-fpm || true

# Configure PHP
echo "date.timezone = UTC" >> /usr/local/etc/php/conf.d/timezone.ini

# Use Azure's provided PORT environment variable
SERVER_PORT=${PORT:-8080}

# Start PHP-FPM (using Unix socket instead of port)
php-fpm --fpm-config /usr/local/etc/php-fpm.d/www.conf &

# Generate nginx config with dynamic port
cat > /etc/nginx/nginx.conf <<EOF
worker_processes auto;
events { worker_connections 1024; }

http {
    server {
        listen $SERVER_PORT;
        server_name localhost;
        root /home/site/wwwroot/public;
        index index.php;

        location / {
            try_files \$uri \$uri/ /index.php?\$query_string;
        }

        location ~ \.php\$ {
            fastcgi_pass unix:/var/run/php-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            include fastcgi_params;
        }
    }
}
EOF

# Start Nginx
nginx -g "daemon off;"