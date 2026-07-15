# Image de l'application SkillHub (Symfony 8 / PHP 8.4)
# Serveur web inclus grâce à FrankenPHP (sert automatiquement public/)
FROM dunglas/frankenphp:1-php8.4

# Extensions PHP requises par le projet :
# - pdo_pgsql : PostgreSQL (Doctrine ORM)
# - mongodb   : MongoDB (Doctrine ODM)
# - intl, zip, opcache : classiques Symfony
RUN install-php-extensions \
    pdo_pgsql \
    mongodb \
    intl \
    zip \
    opcache

# Composer (copié depuis l'image officielle)
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

# Env prod dès le build : évite de charger les bundles de dev (MakerBundle...)
ENV APP_ENV=prod
ENV APP_DEBUG=0

WORKDIR /app

# 1) On installe d'abord les dépendances (couche cache Docker)
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress

# 2) On copie le code puis on finalise l'install
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress \
    && composer dump-autoload --optimize

# FrankenPHP écoute en HTTP sur le port 80
ENV SERVER_NAME=:80
EXPOSE 80

# Droits d'écriture sur le cache/logs Symfony
RUN chown -R www-data:www-data var/ 2>/dev/null || true
