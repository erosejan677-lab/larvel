# Configure PHP-FPM to listen on port 9000
RUN sed -i 's/^listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^listen\.allowed_clients = .*/listen.allowed_clients = 127.0.0.1/' /usr/local/etc/php-fpm.d/www.conf

# Increase PHP upload limits
RUN echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Remove default Nginx config and create new one
RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/* /etc/nginx/nginx.conf
RUN mkdir -p /etc/nginx/conf.d

# Create minimal nginx.conf
RUN printf "user www-data;\n\
worker_processes auto;\n\
pid /run/nginx.pid;\n\
error_log /var/log/nginx/error.log;\n\
events {\n\
    worker_connections 768;\n\
}\n\
http {\n\
    sendfile on;\n\
    tcp_nopush on;\n\
    types_hash_max_size 2048;\n\
    include /etc/nginx/mime.types;\n\
    default_type application/octet-stream;\n\
    access_log /var/log/nginx/access.log;\n\
    include /etc/nginx/conf.d/*.conf;\n\
}\n" > /etc/nginx/nginx.conf

# Create Laravel Nginx config with increased upload size
RUN printf "server {\n\
    listen 80 default_server;\n\
    listen [::]:80 default_server;\n\
    server_name _;\n\
    client_max_body_size 20M;\n\
    root /var/www/html/public;\n\
    index index.php;\n\
\n\
    location / {\n\
        try_files \$uri \$uri/ /index.php?\$query_string;\n\
    }\n\
\n\
    location ~ \.php$ {\n\
        include fastcgi_params;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n\
    }\n\
\n\
    location ~ /\.ht {\n\
        deny all;\n\
    }\n\
}\n" > /etc/nginx/conf.d/laravel.conf
