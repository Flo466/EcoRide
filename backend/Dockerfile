FROM php:8.3-fpm

# Installation des dépendances système et du client MySQL
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    libicu-dev \
    default-mysql-client && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd pdo pdo_mysql intl && \
    rm -rf /var/lib/apt/lists/*

# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Dossier de travail
WORKDIR /var/www/html

EXPOSE 8000

# Commande par défaut : on lance le serveur interne de PHP
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]