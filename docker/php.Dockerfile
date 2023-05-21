# 使用最新版本的 PHP 和 Apache 镜像作为基础镜像
FROM php:8.1-apache

# 设置工作目录
WORKDIR /var/www/html

# 更新软件包列表并安装所需软件包
RUN apt-get update && \
    apt-get install -y wget git zip unzip libzip-dev

# 安装 PHP 扩展
RUN docker-php-ext-install mysqli pdo pdo_mysql fileinfo zip

# 配置 Apache
RUN echo "<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks MultiViews\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
\n\
RewriteEngine On\n\
RewriteCond %{REQUEST_FILENAME} !-f\n\
RewriteCond %{REQUEST_FILENAME} !-d\n\
RewriteRule ^(.*)$ /index.php?s=$1 [L]" >> /etc/apache2/sites-available/000-default.conf

# 设置 Apache 文档根目录并启用 mod_rewrite 模块
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# 复制应用程序代码到容器中
COPY . .

# 安装 Composer 依赖
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN wget https://getcomposer.org/installer -O composer.phar && \
    cd /var/www/html/ && \
    php composer.phar && \
    php composer.phar install
