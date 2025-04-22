#!/bin/sh
set -e

echo "⏳ Esperando a que la base de datos esté lista..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Esperando a que Doctrine se conecte a la base de datos..."
  sleep 2
done

php bin/console cache:clear

echo "✅ Base de datos lista."
echo "🎯 Ejecutando migraciones de Symfony..."
php bin/console doctrine:migrations:migrate --no-interaction || true


echo "🚀 Iniciando PHP-FPM..."
exec php-fpm
