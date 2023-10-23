FROM php:8.0-cli

RUN apt-get update && apt-get install -y git unzip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY oauth-server /var/www/oauth-server

WORKDIR /var/www/oauth-server
RUN composer install

WORKDIR /var/www/oauth-server/examples
RUN composer install
RUN openssl genrsa -out private.key 2048 && openssl rsa -in private.key -pubout > public.key
RUN chmod 600 private.key public.key

WORKDIR /var/www/oauth-server/examples/public
EXPOSE 4444
ENTRYPOINT ["php", "-S", "0.0.0.0:4444"]
