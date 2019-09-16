#!/usr/bin/env bash

#Show PHP versions
echo "----- versions"
version=$(php -v | grep -Eo "PHP [0-9\.]+");
echo "PHP          --> $version"
version=`composer --version | grep -Po '\d.\d.\d '`
echo "Composer     --> $version"

# remove composer complaint about being run as root, we're inside a docker container it's ok
export COMPOSER_ALLOW_SUPERUSER=1

echo "----- Composer install"
cd /var/www && composer install --no-interaction || exit 1
#echo "----- install backend"
#cd /data/backend && composer install --no-interaction || exit 1
#echo "----- migrate database"
#cd /data/backend && ./bin/console doctrine:migrations:migrate latest || exit 1

# fix access rights to cache:
# our composer installs created them with root owner, while apache runs as www
chmod -Rf 777 /var/www/var

apache2-foreground
