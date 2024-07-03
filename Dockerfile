FROM php:8.1-fpm-bullseye

WORKDIR /var/www/html

# Instala os pacotes necessários e define o fuso horário
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    tzdata \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql \
    && cp /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime \
    && echo "America/Sao_Paulo" > /etc/timezone \
    && echo "date.timezone=America/Sao_Paulo" > /usr/local/etc/php/conf.d/timezone.ini

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia apenas os arquivos necessários do código-fonte
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY .env.example .env

# Exponha a porta 8033 e inicie o servidor PHP-FPM
EXPOSE 8044
CMD ["php","artisan","serve","--host=0.0.0.0","--port","8044"]
