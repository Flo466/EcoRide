#!/bin/bash
# init_db.sh - Initializes database and starts Symfony server

# Environment variables (MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)
# are expected to be passed via docker-compose.yml.

# --- Wait for MySQL database service to be ready ---
echo "Waiting for MySQL at $MYSQL_HOST to be ready..."
until mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT 1;" > /dev/null 2>&1; do
  echo "MySQL is unavailable - sleeping"
  sleep 1
done
echo "MySQL is up - starting database setup."

# --- Create database with UTF-8 if it doesn't exist ---
echo "Ensuring database '$MYSQL_DATABASE' exists with UTF-8 encoding..."
mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || { echo "Database creation/check failed!"; exit 1; }


# --- Run Doctrine Migrations ---
echo "Running Symfony Doctrine Migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || { echo "Doctrine migrations failed! Exiting."; exit 1; }

# --- Insert fake data ---
# Check for data.sql in common paths within the container
if [ -f "/var/www/html/src/sql/data.sql" ]; then
    echo "Importing fake data from /var/www/html/src/sql/data.sql..."
    mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" --default-character-set=utf8mb4 < /var/www/html/src/sql/data.sql || { echo "Data import failed! Exiting."; exit 1; }
elif [ -f "/var/www/html/data.sql" ]; then
    echo "Importing fake data from /var/www/html/data.sql..."
    mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" --default-character-set=utf8mb4 < /var/www/html/data.sql || { echo "Data import failed! Exiting."; exit 1; }
else
    echo "Warning: data.sql not found at expected paths (/var/www/html/src/sql/data.sql or /var/www/html/data.sql). Skipping data import."
fi

echo "Database setup complete. Starting Symfony built-in server..."
# --- Start Symfony built-in server ---
exec php -S 0.0.0.0:8000 -t public