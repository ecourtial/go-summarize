.PHONY: start stop php nginx mysql test linter stan lines audit dump-migration generate-migration install

DOCKER_COMPOSE_EXEC = docker compose exec

## —— 🎶 The go-summarize server Makefile 🎶 ——————————————————————————————————————————
help: ## Outputs this help screen.
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Environment 🚀 —————————————————————————————————————————————————————————————————
start: ## Starts the stack
	docker compose up -d
#&&\
#${DOCKER_COMPOSE_EXEC} php bash -c 'composer install'

stop: ## Stops the stack
	docker compose stop

php: ## Access to the PHP container
	${DOCKER_COMPOSE_EXEC} php bash

## —— Coding standards/lints/tools ✨ ————————————————————————————————————————————————
linter: ## Run PHPCsFixer in fix mode
	${DOCKER_COMPOSE_EXEC} php bash -c 'vendor/bin/php-cs-fixer fix'

stan: ## Run PHPStan and generate the baseline
	${DOCKER_COMPOSE_EXEC} php bash -c 'APP_ENV=test bin/console cache:warmup && vendor/bin/phpstan clear-result-cache && php vendor/bin/phpstan analyse --memory-limit=-1 --generate-baseline'

lines: ## Display data about the code
	${DOCKER_COMPOSE_EXEC} php bash -c 'vendor/bin/lines measure src'

audit: ## Launch composer audit
	${DOCKER_COMPOSE_EXEC} php bash -c 'composer audit'
