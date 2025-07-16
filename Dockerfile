FROM php:8.2-fpm

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    && docker-php-ext-install \
    curl \
    dom \
    mbstring \
    pdo_mysql \
    simplexml \
    xml

WORKDIR /app
COPY . .

CMD ["php-fpm"]
