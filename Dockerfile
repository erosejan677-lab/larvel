# Use stable PHP + Nginx base image
FROM richarvey/nginx-php-fpm:3.1.6

# Copy the entire project
COPY . .

# Create .env file with your database credentials
RUN echo "APP_NAME=larvel" >> .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_DEBUG=true" >> .env && \
    echo "APP_KEY=base64:FPdiQPdfo0IIk91I5PwmUiykiL4UgdVI1KSCYmFO8c0=" >> .env && \
    echo "DB_CONNECTION=pgsql" >> .env && \
    echo "DB_HOST=aws-1-ap-south-1.pooler.supabase.com" >> .env && \
    echo "DB_PORT=6543" >> .env && \
    echo "DB_DATABASE=postgres" >> .env && \
    echo "DB_USERNAME=postgres.ygwxwlbvgblvmxwuyaox" >> .env && \
    echo "DB_PASSWORD=umairkhan816" >> .env && \
    echo "IMAGEKIT_PUBLIC_KEY=public_tA4ShnDPwLDHBswKfE0WTdDxU0k=" >> .env && \
    echo "IMAGEKIT_PRIVATE_KEY=private_zFt9+YCaE4TSp8KU6qRhYDA8Sh4=" >> .env && \
    echo "IMAGEKIT_URL_ENDPOINT=https://ik.imagekit.io/closyyyy" >> .env

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Run migrations (don't fail if tables already exist)
RUN php artisan migrate --force || true

# Laravel optimizations
RUN php artisan storage:link
RUN php artisan config:clear
RUN php artisan config:cache
RUN php artisan route:clear
RUN php artisan route:cache
RUN php artisan view:cache

# Force correct permissions
RUN chmod -R 777 storage bootstrap/cache

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

# Create nginx.conf with memory buffering (no disk writes!)
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
    client_max_body_size 20M;\n\
    client_body_buffer_size 20M;\n\
    include /etc/nginx/conf.d/*.conf;\n\
}\n" > /etc/nginx/nginx.conf

# Create Laravel Nginx config
RUN printf "server {\n\
    listen 80 default_server;\n\
    listen [::]:80 default_server;\n\
    server_name _;\n\
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

# Environment variables
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# Start container
CMD ["/start.sh"]
