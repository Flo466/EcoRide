#!/bin/sh
set -e # Arrête le script si une commande échoue
set -x # Affiche les commandes exécutées (pour le débogage)

# NOTE IMPORTANTE : Assurez-vous que ce fichier est enregistré avec des fins de ligne UNIX (LF),
# et non Windows (CRLF), pour éviter les erreurs "exec format error" dans les conteneurs Linux.

# Attendre que le service MariaDB soit prêt
echo "Attente de MariaDB (db:3306)..."
/usr/local/bin/wait-for-it.sh db:3306 --timeout=60 --strict -- echo "MariaDB est prêt."

# --- RÉ-AJOUTÉ POUR DÉBOGAGE : Nettoyage et création de la base de données ---
# Ceci garantit une base de données vierge à chaque démarrage pour les migrations.
# Nous le retirerons une fois le problème résolu.
echo "Tentative de suppression de la base de données (si elle existe)..."
# Utilise --if-exists pour éviter une erreur si la base n'existe pas.
php bin/console doctrine:database:drop --force --if-exists 2>&1 || { echo "AVERTISSEMENT: La base de données n'a pas pu être supprimée ou n'existait pas." >&2; }

echo "Création de la base de données..."
php bin/console doctrine:database:create 2>&1 || { echo "Erreur FATALE lors de la création de la base de données" >&2; exit 1; }
# --- FIN RÉ-AJOUT ---

# Vider le cache Symfony
echo "Vider le cache Symfony..."
# Exécute la commande et redirige la sortie vers stderr et stdout pour les logs Docker
php bin/console cache:clear --env=prod --no-debug 2>&1 || { echo "Erreur lors du vidage du cache Symfony" >&2; exit 1; }

# Exécuter les migrations Doctrine
# Cette commande est idempotente et ne s'exécutera que si de nouvelles migrations sont disponibles.
echo "Exécution des migrations Doctrine..."
# Exécute la commande et redirige la sortie vers stderr et stdout pour les logs Docker
# IMPORTANT : Nous utilisons '|| true' ici pour que le script continue même si la migration échoue.
# Ceci nous permettra de voir si PHP-FPM démarre et si l'application Symfony affiche une erreur plus utile qu'un 502.
php bin/console doctrine:migrations:migrate --no-interaction 2>&1 || { echo "AVERTISSEMENT: La migration a échoué, mais le conteneur va tenter de démarrer PHP-FPM." >&2; }

# Exécuter d'autres scripts post-démarrage si nécessaire
# echo "Installation des assets publics..."
# php bin/console assets:install public --symlink --relative 2>&1 || { echo "Erreur lors de l'installation des assets" >&2; exit 1; }

# Exécuter la commande principale du conteneur (php-fpm dans notre cas)
echo "Démarrage de PHP-FPM..."
exec "$@"
