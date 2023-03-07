ARG NODE_VERSION=14

FROM node:${NODE_VERSION}-alpine AS sylius_nodejs

WORKDIR /srv/sylius

RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		bash \
	;
