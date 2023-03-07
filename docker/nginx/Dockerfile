ARG NGINX_VERSION=1.16

FROM nginx:${NGINX_VERSION}-alpine AS sylius_nginx

COPY conf.d/default.conf /etc/nginx/conf.d/
