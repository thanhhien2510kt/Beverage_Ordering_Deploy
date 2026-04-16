FROM php:8.2-apache

# Cập nhật và cài đặt các thư viện cần thiết cho PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Kích hoạt module rewrite của Apache (giúp nhận diện route tốt hơn)
RUN a2enmod rewrite

# Sửa lỗi cảnh báo ServerName của Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy toàn bộ code hiện tại vào thư mục chạy web của Apache
COPY . /var/www/html/

# Cấp quyền đọc/ghi cho thư mục web
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Mở cổng 80 (để Render trỏ traffic vào đây theo mặc định của Apache)
EXPOSE 80
