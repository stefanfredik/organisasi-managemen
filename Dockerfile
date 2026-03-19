FROM php:8.4-fpm

# Arguments for User/Group ID
ARG USER_ID=1000
ARG GROUP_ID=1000

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpq-dev \
    libmemcached-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    intl \
    zip \
    opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js and NPM
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs

# Create system user to run Composer and Artisan Commands
RUN groupadd -g ${GROUP_ID} organisasi && \
    useradd -u ${USER_ID} -g organisasi -m -s /bin/bash organisasi

# Set permissions for the working directory
RUN chown -R organisasi:organisasi /var/www/html

# Switch to user
USER organisasi

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
