#!/bin/bash

crowdr_project="wakenbake"
crowdr_name_format="%s-%s"

crowdr_config="
fpm build docker/fpm
fpm workdir /app
fpm volume $(pwd):/app
fpm volume $(pwd)/ssh:/root/.ssh/
fpm network host
fpm after.start update_known_hosts

# nginx webserver
nginx image nginx:latest
nginx volume $(pwd):/app
nginx volume $(pwd)/docker/nginx/nginx.conf:/etc/nginx/nginx.conf:ro
nginx volume $(pwd)/docker/nginx/sites-enabled/:/etc/nginx/conf.d/
nginx network host

"

update_known_hosts() {
    docker exec -it $(crowdr_fullname fpm) console app:ssh:update-known-hosts
}
