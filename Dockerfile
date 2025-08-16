# Use official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
	git \
	unzip \
	libzip-dev \
	libpng-dev \
	libonig-dev \
	libxml2-dev \
	&& docker-php-ext-install \
	pdo_mysql \
	mbstring \
	exif \
	pcntl \
	bcmath \
	gd \
	zip \
	&& a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev dependencies)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
	&& chmod -R 755 /var/www/html/storage

# Configure Apache
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s \
	CMD curl -f http://localhost/health || exit 1

# Start Apache in foreground
CMD ["apache2-foreground"]