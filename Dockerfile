FROM php:8.2-apache

# 安裝 PHP 必要的擴展 (MySQL 驅動)
RUN docker-php-ext-install pdo pdo_mysql

# 啟用 Apache 的 Rewrite 模組
RUN a2enmod rewrite

# 設定工作目錄
WORKDIR /var/www/html

# 調整權限
RUN chown -R www-data:www-data /var/www/html
