#!/bin/sh
set -e

# Ensure the persisted uploads directory exists and is writable by php-fpm
mkdir -p storage/app/public
chown -R www-data:www-data storage/app/public

# Public symlink so uploaded files are served under /storage
php artisan storage:link --quiet || true

# Cache configuration for performance.
# Note: route:cache is intentionally skipped because routes/api.php uses a closure,
# and there are no Blade views to cache (this is an API-only application).
php artisan config:cache
php artisan event:cache

exec "$@"
