web: php -d display_errors=0 -S 0.0.0.0:${PORT:-8080} -t public/
release: php artisan migrate --force && php artisan passport:keys --force || true && php artisan config:cache && php artisan route:cache
