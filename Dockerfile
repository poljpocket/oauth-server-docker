FROM php:8.0-cli

RUN apt-get update && apt-get install -y git unzip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY ./src /var/www/sso-test-server/src
COPY ./index.php /var/www/sso-test-server
COPY ./composer.json /var/www/sso-test-server

WORKDIR /var/www/sso-test-server
RUN composer install

RUN openssl genrsa -out private.key 2048 && openssl rsa -in private.key -pubout > public.key
RUN chmod 600 private.key public.key

EXPOSE 4444
ENTRYPOINT ["php", "-S", "0.0.0.0:4444"]
