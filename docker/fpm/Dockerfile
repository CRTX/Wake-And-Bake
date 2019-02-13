FROM php:7-fpm

ENV PATH /app/server/php/bin/:$PATH

RUN apt update && apt install -y \
    etherwake \
    openssh-client \
    sshpass \
&& rm -rf /var/lib/apt/lists/*

COPY www.conf /usr/local/etc/php-fpm.d/www.conf

CMD ["php-fpm", "-R"]
