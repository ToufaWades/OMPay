#!/bin/sh

echo "Starting docker-entrypoint.sh"

# Ensure storage & cache directories exist
echo "Ensuring storage directories exist..."
mkdir -p storage/framework/{cache,data,sessions,testing,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chown -R laravel:laravel storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Check required DB envs
MISSING=0
for v in DB_HOST DB_PORT DB_DATABASE DB_USERNAME; do
  eval val="\$$v"
  if [ -z "$val" ]; then
    echo "Required env $v is not set"
    MISSING=1
  fi
done

if [ "$MISSING" -eq 1 ]; then
  echo "Missing DB variables — exiting."
  exit 1
fi

# Generate APP_KEY if needed
if [ -z "$APP_KEY" ]; then
  echo "APP_KEY not set — generating one"
  php artisan key:generate --force || true
fi

echo "Waiting for database..."
MAX_WAIT=120
WAITED=0
while ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" >/dev/null 2>&1; do
  if [ "$WAITED" -ge "$MAX_WAIT" ]; then
    echo "Timeout waiting for DB after ${MAX_WAIT}s" >&2
    exit 1
  fi
  echo "DB not ready — sleep 1 ( waited ${WAITED}s )"
  sleep 1
  WAITED=$((WAITED+1))
done

echo "DB is ready — running migrations"
php artisan route:clear || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan migrate --force || true

echo "Starting Laravel..."
exec "$@"
