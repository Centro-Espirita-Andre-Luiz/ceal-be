# syntax=docker/dockerfile:1
ARG VARIANT=8.3
FROM mcr.microsoft.com/devcontainers/php:${VARIANT}

# Install system dependencies and Node
ARG NODE_VERSION=20
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        mariadb-client \
    && docker-php-ext-install pdo pdo_mysql intl zip gd xml \
    && curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (if not already present)
RUN if [ ! -x "/usr/local/bin/composer" ]; then \
      curl -sS https://getcomposer.org/installer -o composer-setup.php \
      && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
      && rm composer-setup.php; \
    fi

# Enable Xdebug (optional for coverage)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "zend_extension=$(php -r 'echo ini_get(\"extension_dir\");')/xdebug.so" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode=debug,develop,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Create non-root user (devcontainers base may already have 'vscode')
ARG USERNAME=vscode
RUN usermod -a -G www-data ${USERNAME}

WORKDIR /workspaces/ceal-be
