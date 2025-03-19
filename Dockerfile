FROM php:8.2-fpm-alpine

# Establece variables de entorno para evitar interacciones innecesarias durante la instalación
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/var/www/.composer

# Instala dependencias básicas y herramientas necesarias
RUN apk update && apk add --no-cache \
    mariadb-client \
    zip \
    unzip \
    git \
    curl \
    nano \
    autoconf \
    g++ \
    make \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    freetype-dev \
    libpng-dev \
    jpeg-dev \
    zlib-dev \
    openssl \
    openssl-dev

# Habilita las extensiones PHP necesarias (pero sin incluirlas en el docker-php-ext-install)
RUN docker-php-ext-install pdo_mysql session fileinfo tokenizer dom zip mbstring

# Activa la extension OpenSSL: Esto se moverá del command al Dockerfile
RUN docker-php-ext-enable openssl

# Instala la extensión Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instala GD
RUN apk add --no-cache freetype libpng libjpeg libjpeg-turbo-dev freetype-dev libpng-dev
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install -j "$(nproc)" gd

# Limpia las herramientas de desarrollo para reducir el tamaño de la imagen
RUN apk del gcc g++ make libc-dev && rm -rf /var/cache/apk/*

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos del proyecto
COPY . .

# Instala las dependencias de Composer
RUN composer install --no-interaction --no-plugins --no-scripts --optimize-autoloader

# Instala dependencias de Node.js y construye assets
RUN npm install
RUN npm run build

# Configura permisos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/bootstrap/cache

# Expone el puerto 8000 para el servidor de desarrollo
EXPOSE 8000

# Comando para ejecutar el servidor de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
