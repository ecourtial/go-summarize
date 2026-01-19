FROM nginx:1.16-alpine

RUN apk add --update --no-cache curl wget jq bash bash-completion grep nano

ENV PHP_FPM_UPSTREAM=php:9000 \
    DNS_RESOLVER=127.0.0.11

WORKDIR /var/www/html

CMD ["/bin/sh", "-c", "envsubst '$PHP_FPM_UPSTREAM,$DNS_RESOLVER' < /etc/nginx/conf.d/vhost.template > /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]
