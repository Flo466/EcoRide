# Dockerfile pour l'application PHP (Symfony)

# Étape 1 : Image de base
# Utilise l'image officielle de PHP 8.3 avec FPM (FastCGI Process Manager) sur Alpine Linux.
FROM php:8.3-fpm-alpine

# Étape 2 : Définir le répertoire de travail
# C'est le répertoire où ton code d'application sera placé à l'intérieur du conteneur.
WORKDIR /var/www/html

# Étape 3 : Créer le répertoire et définir les permissions
# S'assure que le répertoire existe et que l'utilisateur 'www-data' (utilisateur par défaut de PHP-FPM)
# a les droits nécessaires.
RUN mkdir -p /var/www/html && chown www-data:www-data /var/www/html

# Étape 4 : Mettre à jour les dépôts de paquets Alpine
# Important pour s'assurer que nous installons les dernières versions des paquets.
RUN apk update

# Étape 5 : Installer les dépendances système nécessaires
# Ces paquets sont des outils et des bibliothèques requis pour compiler les extensions PHP
# ou pour que Symfony fonctionne correctement.
RUN apk add --no-cache \
    curl \
    mysql-client \
    git \
    build-base \
    autoconf \
    gcc \
    g++ \
    make \
    libzip-dev \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    postgresql-dev \
    openssl-dev \
    libtool \
    pcre-dev \
    re2c \
    zlib-dev \
    php-pear

# Étape 6 : Installer les extensions PHP standard
# Ces extensions sont nécessaires pour une application Symfony typique.
RUN docker-php-ext-install -j$(nproc) pdo_mysql opcache gd intl zip exif

# Étape 7 : Configurer PHP-FPM et opcache
# Déplace le fichier de configuration de production par défaut et active l'opcache
# pour de meilleures performances.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "opcache.enable=1" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.enable_cli=1" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.revalidate_freq=0" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.validate_timestamps=1" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.max_accelerated_files=10000" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.memory_consumption=128" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.interned_strings_buffer=8" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.fast_shutdown=1" >> "$PHP_INI_DIR/php.ini"

# Étape 8 : Installer Composer
# Copie l'exécutable Composer depuis son image officielle.
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Étape 9 : Copier le code de ton application
# Copie tout le contenu du répertoire courant (ton projet Symfony) dans le conteneur.
COPY --chown=www-data:www-data . /var/www/html

# Étape 10 : Mettre à jour Composer et vider son cache
RUN composer self-update --2
RUN composer clear-cache

# Étape 11 : Installer les dépendances Composer
# Installe les bibliothèques PHP requises par ton projet.
# L'option --ignore-platform-req=ext-mongodb n'est plus nécessaire ici car l'application est propre.
RUN composer install -vvv --optimize-autoloader --no-interaction --no-scripts

# Étape 12 : Rendre le script Symfony console exécutable
RUN chmod +x bin/console

# Étape 13 : Vider le cache Symfony
# Cette étape est exécutée pendant le build. Elle devrait fonctionner maintenant.
RUN php bin/console cache:clear --env=prod --no-debug

# Étape 14 : Définir la commande par défaut pour le conteneur
CMD ["php-fpm"]
