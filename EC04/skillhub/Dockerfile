# syntax=docker/dockerfile:1
# Image durcie de l'application SkillHub (Symfony / PHP 8.4 + FrankenPHP)
# - multi-stage : les outils de build ne restent pas dans l'image finale
# - utilisateur non-root
# - HEALTHCHECK intégré

# ============ Étape 1 : construction (builder) ============
FROM dunglas/frankenphp:1-php8.4 AS builder

RUN install-php-extensions pdo_pgsql mongodb intl zip opcache
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

ENV APP_ENV=prod
ENV APP_DEBUG=0
WORKDIR /app

# 1) Dépendances d'abord (couche cache Docker)
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress

# 2) Puis le code, et finalisation
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress \
    && composer dump-autoload --optimize --no-dev

# ============ Étape 2 : image finale (runtime) ============
FROM dunglas/frankenphp:1-php8.4 AS runtime

# Extensions PHP nécessaires à l'exécution
RUN install-php-extensions pdo_pgsql mongodb intl zip opcache

# Mise à jour des paquets système (patche les failles corrigeables) + curl (HEALTHCHECK)
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends curl \
    && rm -rf /var/lib/apt/lists/*

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV SERVER_NAME=:80
WORKDIR /app

# On copie uniquement l'application déjà construite (sans les outils de build)
COPY --from=builder /app /app

# FrankenPHP tourne en utilisateur NON-root :
# on l'autorise à écouter sur le port 80, et on donne les droits à www-data.
RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R www-data:www-data /app /data /config

USER www-data

EXPOSE 80

# Vérifie automatiquement que l'application répond
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD curl -fsS http://localhost:80/ || exit 1
