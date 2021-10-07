rm -R var/cache/prod
php bin/console cache:clear --no-warmup --env=prod
