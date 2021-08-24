rm -R var/cache/dev
php bin/console cache:clear --no-warmup --env=dev
