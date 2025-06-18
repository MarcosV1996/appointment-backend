FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql mbstring exif pcntl bcmath

# 2. Instala extensões PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 3. Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Configura diretório de trabalho
WORKDIR /var/www/html

# 5. Configura Git para ignorar problemas de ownership
RUN git config --global --add safe.directory /var/www/html

# 6. Copia APENAS os arquivos necessários para instalação de dependências
COPY composer.json composer.lock ./

# 7. Instala dependências
RUN composer install --optimize-autoloader --no-scripts --no-dev

# 8. Copia TODO o resto do projeto
COPY . .

# 9. Configura permissões
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage

# 10. Configura Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# 11. Gera chave da aplicação
RUN php artisan key:generate

# 12. Configura storage
RUN mkdir -p storage/app/public && \
    mkdir -p database && \
    touch database/database.sqlite && \
    php artisan storage:link

# 13. Porta para PHP-FPM
EXPOSE 9000

# 14. Comando para iniciar serviços
CMD ["sh", "-c", "service nginx start && php-fpm"]