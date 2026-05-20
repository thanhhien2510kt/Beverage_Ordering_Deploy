# syntax=docker/dockerfile:1
FROM php:8.2-apache

# Install PHP extensions — BuildKit cache mount giữ apt cache giữa các lần build
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && apt-get install -y \
    libpq-dev \
    libonig-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        mbstring

# Enable mod_rewrite
RUN a2enmod rewrite

# PHP config — enable output_buffering (XAMPP default, Docker tắt)
RUN echo "output_buffering = 4096" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/custom.ini

# Set document root to project root
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Copy source
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/assets/img

EXPOSE 80
