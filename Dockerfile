FROM php:8.2-cli
WORKDIR /app
COPY . /app
# Install pdo_sqlite dependencies
RUN apt-get update \
    && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && mkdir -p /app/data \
    && chown -R www-data:www-data /app/data
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]