FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos al servidor web
COPY . /var/www/html/

# Permisos (si subes imágenes)
RUN chown -R www-data:www-data /var/www/html