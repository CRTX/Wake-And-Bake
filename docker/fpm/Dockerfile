FROM php:7-fpm

RUN apt update && apt install -y etherwake
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

CMD ["php-fpm", "-R"]
