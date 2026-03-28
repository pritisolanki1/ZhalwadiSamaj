web: php -d display_errors=0 -d upload_max_filesize=100M -d post_max_size=256M -d memory_limit=512M -S 0.0.0.0:${PORT:-8080} -t public/
release: php artisan migrate --force && php artisan passport:keys --force || true && php artisan config:cache && php artisan route:cache
