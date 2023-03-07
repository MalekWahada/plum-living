DOCKER_COMPOSE          = docker-compose -p plum
DOCKER_EXEC             = $(DOCKER_COMPOSE) exec
EXEC_PHP                = $(DOCKER_EXEC) php
SYMFONY                 = $(EXEC_PHP) php bin/console
EXEC_DB                 = $(DOCKER_EXEC) mysql
EXEC_NODE               = $(DOCKER_EXEC) nodejs

#################################
Docker:

docker-files: .env
.PHONY: docker-files

build:
	$(DOCKER_COMPOSE) build
.PHONY: build

## Start containers
.PHONY: up
up:
	$(DOCKER_COMPOSE) up -d --remove-orphans

## Remove containers
.PHONY: down
down:
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --remove-orphans

.env: .env.local
	@if [ -f .env ]; \
	then\
		echo '\033[1;41m/!\ The .env.local file has changed. Please check your .env file (this message will not be displayed again).\033[0m';\
		touch .env;\
		exit 1;\
	else\
		echo cp .env.local .env;\
		cp .env.local .env;\
	fi

# Wait for containers' services to be fully started
wait-db:
	@$(EXEC_PHP) php -r "set_time_limit(60);for(;;){if(@fsockopen('mysql',3306))die;echo 'Waiting for database... ';sleep(1);}"

.PHONY: wait-db

#################################
Project:

.PHONY: clean-cache
clean-cache:
	$(EXEC_PHP) rm -rf var/cache/*

## Install project environment from scratch
.PHONY: install
install: down build up vendor node_modules assets-dev
	$(SYMFONY) asset:install
	@echo Installation is finished, please configure .env file

.PHONY: uninstall
uninstall: clean-cache
	$(EXEC_PHP) rm -rf vendor/
	$(DOCKER_COMPOSE) rm -v -f -s
	docker volume rm plum_mysql-data

## Start containers (unpause)
start:
	@$(DOCKER_COMPOSE) unpause || true
	@$(DOCKER_COMPOSE) start || true

## Stop containers (pause)
stop:
	@$(DOCKER_COMPOSE) stop || true

# rules based on files
composer.lock: composer.json
	@echo composer.lock is not up to date.

## Run composer install
vendor: composer.lock
	$(EXEC_PHP) composer install
	$(EXEC_PHP) chown -R 1000:1000 vendor/

## Run php
php:
	$(EXEC_PHP) bash
node:
	$(EXEC_NODE) bash

## Run yarn install
node_modules: yarn.lock
	$(EXEC_NODE) yarn install

.PHONY: reset start install cc

#################################
Database:

## Run MySQL
mysql:
	$(EXEC_DB) bash

#################################
Frontend:

## Recompile frontend assets automatically when files change
assets-watch:
	@$(EXEC_NODE) yarn encore dev --watch

## Build frontend dev assets
assets-dev:
	@$(EXEC_NODE) yarn encore dev

## Build frontend production assets
assets-build:
	@$(EXEC_NODE) yarn encore production

#################################
Quality:

## Run all quality assurance tools
quality: cs-fixer phpcs phpstan #psalm

## Apply php-cs-fixer fixes
cs-fixer:
	$(EXEC_PHP) vendor/bin/php-cs-fixer fix --diff --verbose --using-cache=no src

## Check for php-cs-fixer fixes
cs-fixer-check:
	$(EXEC_PHP) vendor/bin/php-cs-fixer fix --diff --verbose --using-cache=no --dry-run src

## PHPCS
phpcs:
	$(EXEC_PHP) vendor/bin/phpcs --runtime-set ignore_warnings_on_exit true

## PHPStan
phpstan:
	$(EXEC_PHP) vendor/bin/phpstan analyse -c phpstan.neon -l 6 src/

## Psalm
psalm:
	$(EXEC_PHP) vendor/bin/psalm

#################################
Tests:

## TODO

#################################
.DEFAULT_GOAL := help

ifneq ($(OS), Windows_NT)
# COLORS
GREEN  := @$(shell tput -Txterm setaf 2)
YELLOW := @$(shell tput -Txterm setaf 3)
WHITE  := @$(shell tput -Txterm setaf 7)
RESET  := @$(shell tput -Txterm sgr0)
TARGET_MAX_CHAR_NUM=25

help:
	@echo "${GREEN}Plum-kitchen website${RESET}"
	@awk '/^[a-zA-Z0-9\-]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf "  ${YELLOW}%-$(TARGET_MAX_CHAR_NUM)s${RESET} ${GREEN}%s${RESET}\n", helpCommand, helpMessage; \
		} \
		isTopic = match(lastLine, /^#################################/); \
        if (isTopic) { printf "\n%s\n", $$1; } \
	} { lastLine = $$0 }' $(MAKEFILE_LIST)
else
help:
	@echo No help for windows
endif
