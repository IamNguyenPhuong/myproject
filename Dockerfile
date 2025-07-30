FROM php:8.2-apache

# Copy code vào container
COPY . /var/www/html/

# Kích hoạt mod_rewrite nếu bạn dùng .htaccess
RUN a2enmod rewrite

# Cấp quyền đúng cho Apache
RUN chown -R www-data:www-data /var/www/html
