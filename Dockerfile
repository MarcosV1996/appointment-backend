# Use a imagem oficial do PHP com FPM
FROM php:8.0-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git

# Instalar extensões do PHP necessárias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Definir o diretório de trabalho no container
WORKDIR /var/www

# Copiar o conteúdo do repositório para o container
COPY . .

# Instalar o Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Instalar dependências do Laravel com o Composer
RUN composer install --no-dev --optimize-autoloader

# Expor a porta padrão do PHP-FPM
EXPOSE 9000

# Rodar o PHP-FPM
CMD ["php-fpm"]
