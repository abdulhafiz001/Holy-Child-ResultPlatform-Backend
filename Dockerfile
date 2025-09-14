# Use a base image with Nginx and PHP-FPM
FROM richarvey/nginx-php-fpm:3.1.6

# Copy all application code into the container
COPY . .

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel production config
ENV APP_ENV production
ENV APP_DEBUG false

# Allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Start the application
CMD ["/start.sh"]
