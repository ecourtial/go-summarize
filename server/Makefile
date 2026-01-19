.PHONY: start stop php nginx mysql test linter stan lines audit dump-migration generate-migration install

DOCKER_COMPOSE_EXEC = docker compose exec

## —— 🎶 The go-summarize server Makefile 🎶 ——————————————————————————————————————————
help: ## Outputs this help screen.
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Environment 🚀 —————————————————————————————————————————————————————————————————
start: ## Starts the stack
	docker compose up -d && \
	${DOCKER_COMPOSE_EXEC} php bash -c 'composer install'

stop: ## Stops the stack
	docker compose stop

php: ## Access to the PHP container
	${DOCKER_COMPOSE_EXEC} php bash

nginx: ## Access to the Nginx container
	${DOCKER_COMPOSE_EXEC} nginx bash

mysql: ## Access to the MySQL server
	${DOCKER_COMPOSE_EXEC}  mysql bash -c 'mysql -u root -proot'

## —— Tests ✅✨ —————————————————————————————————————————————————————————————————
test: ## Run all PHPUnit tests and generate the report
	make reset_test_db && \
	${DOCKER_COMPOSE_EXEC} php bash -c 'composer install' \
	${DOCKER_COMPOSE_EXEC} php bash -c 'bin/console cache:clear --env=test' \
	&& ${DOCKER_COMPOSE_EXEC} php bash -c 'XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html phpunit-report --display-phpunit-deprecations'


## —— Coding standards/lints/tools ✨ ————————————————————————————————————————————————
linter: ## Run PHPCsFixer in fix mode
	${DOCKER_COMPOSE_EXEC} php bash -c 'vendor/bin/php-cs-fixer fix'

stan: ## Run PHPStan and generate the baseline
	${DOCKER_COMPOSE_EXEC} php bash -c 'APP_ENV=test bin/console cache:warmup && vendor/bin/phpstan clear-result-cache && php vendor/bin/phpstan analyse --memory-limit=-1 --generate-baseline'

lines: ## Display data about the code
	${DOCKER_COMPOSE_EXEC} php bash -c 'vendor/bin/lines measure src'

audit: ## Launch composer audit
	${DOCKER_COMPOSE_EXEC} php bash -c 'composer audit'


## —— DB ✨💻 ————————————————————————————————————————————————

reset_db: ## Reset the DB
	make reset_db_command ENV=dev

reset_test_db: ## Reset the test DB
	make reset_db_command ENV=test

reset_db_command: ## Do not use directly (subcommand)!
	${DOCKER_COMPOSE_EXEC} php bash -c 'make internal_reset_db_command ENV=$(ENV)' \
	&& docker compose exec php bash -c 'make internal_migrate_db_command ENV=$(ENV)' \
 	&& docker compose exec php bash -c 'make internal_load_fixtures_command ENV=$(ENV)'

generate-migration: ## Generate a new migration.
	${DOCKER_COMPOSE_EXEC} php bash -c 'bin/console doctrine:migrations:diff'

dump-migration: ## Dump the migration content needed to update the DB.
	${DOCKER_COMPOSE_EXEC} php bash -c 'bin/console doctrine:schema:update --dump-sql'

######################################################################
# FOR SETUP ONLY!
######################################################################

## —— Installation ✨💻 ————————————————————————————————————————————————
install: ## Creates and launch the container + force a database reset
	make start \
	&& make reset_db
