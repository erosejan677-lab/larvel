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
    echo "DB_PASSWORD=umairkhan816" >> .env

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Run migrations (add this - very important!)
RUN php artisan migrate --force

# Laravel optimizations (now with .env file present)
RUN php artisan storage:link
RUN php artisan config:clear
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Force correct permissions
RUN chmod -R 777 storage bootstrap/cache

# Create nginx conf.d directory and add configuration
RUN mkdir -p /etc/nginx/conf.d && \
    printf "server {\n\
    listen 80;\n\
    server_name _;\n\
    root /var/www/html/public;\n\
    index index.php index.html;\n\
\n\
    location / {\n\
        try_files \$uri \$uri/ /index.php?\$query_string;\n\
    }\n\
\n\
    location ~ \.php$ {\n\
        include fastcgi_params;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n\
    }\n\
\n\
    location ~ /\.ht {\n\
        deny all;\n\
    }\n\
}\n" > /etc/nginx/conf.d/default.conf

# Environment variables for the base image
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# Start container
CMD ["/start.sh"]
