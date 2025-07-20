FROM php:8.3-fpm

# Install required dependencies and MySQL client
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git default-mysql-client && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd pdo pdo_mysql && \
    rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist

# Make init script executable
RUN chmod +x /var/www/html/init_db.sh

# Expose Symfony server port
EXPOSE 8000

# Run init script
ENTRYPOINT ["./init_db.sh"]

# Default command
CMD []
