#!/bin/bash
set -e

cd /home/site/wwwroot

# Start nginx in the foreground
nginx -c /home/site/wwwroot/nginx.conf -g "daemon off;"
