#!/bin/bash

crowdr_project="wakenbake"
crowdr_name_format="%s-%s"

crowdr_config="
fpm build docker/fpm
fpm workdir /app
fpm volume $(pwd):/app
fpm network host

# nginx webserver
nginx image nginx:latest
nginx volume $(pwd):/app
nginx volume $(pwd)/docker/nginx/nginx.conf:/etc/nginx/nginx.conf:ro
nginx volume $(pwd)/docker/nginx/sites-enabled/:/etc/nginx/conf.d/
nginx network host

"
