FROM php:8.2-apache

# Extensiones PHP necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# ✅ Forzar SOLO un MPM (prefork) y deshabilitar los otros
RUN a2dismod mpm_event mpm_worker || true \
 && a2enmod mpm_prefork

# (Opcional) headers/rewrite si los ocupas
RUN a2enmod rewrite headers

# Copiar tu API
COPY . /var/www/html/

# Permisos (si usas uploads)
RUN chown -R www-data:www-data /var/www/html