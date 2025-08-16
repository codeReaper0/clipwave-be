#!/bin/bash

# Use Azure's assigned port
SERVER_PORT=${PORT:-8080}

# Generate nginx config with proper Slim PHP routing
cat > /etc/nginx/sites-available/default <<EOF
server {
    listen ${SERVER_PORT};
    server_name localhost;
    root /home/site/wwwroot;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(/.+)\$;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Start PHP-FPM with explicit configuration
/usr/sbin/php-fpm -y /etc/php/php-fpm.conf -c /etc/php/php.ini

# Test nginx configuration
nginx -t

# Start Nginx
exec nginx -g 'daemon off;'