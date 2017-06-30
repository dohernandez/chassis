NO_COLOR=\033[0m
OK_COLOR=\033[32;01m
WARN_COLOR=\033[33;01m

PACKAGE_NAME = chassis/chassis
PACKAGE_VERSION = 1.0

# do not edit the following lines
# for shell usage

all: usage

usage:
	@echo "dependencies:  Install application dependencies."
	@echo "dependencies.autoload:  Autoload application dependencies."
	@echo "standards:  Run code standards checks."
	@echo "test:  Run application test."
	@echo "tail.log:  Tail the application log."
	@echo "db.list:  List the doctrine commands."
	@echo "db.generate:  Generate a doctrine migration."
	@echo "db.migrate:  Run a doctrine migration."
	@echo "db.reset:  Reset the database."

dependencies:
	@printf "$(OK_COLOR)==> Installing dependencies...$(NO_COLOR)\n"
	@docker run -it --rm -v $(PWD):/app -w /app prooph/composer:7.1 update --prefer-dist --no-interaction --optimize-autoloader --no-progress

dependencies.autoload:
	@printf "$(OK_COLOR)==> Auto-loading dependencies...$(NO_COLOR)\n"
	@docker run -it --rm -v $(PWD):/app -w /app prooph/composer:7.1 dump-autoload

standards:
	@printf "$(OK_COLOR)==> Checking code standards...$(NO_COLOR)\n"
	@docker run -it --rm -v $(PWD):/app -w /app rcrosby256/php-cs-fixer fix --dry-run --diff
	#@printf "$(OK_COLOR)==> Checking for code mess...$(NO_COLOR)\n"
	#@docker run -it --rm -v $(PWD):/workspace shavenking/docker-phpmd src text ruleset.xml --suffixes php --exclude src/Infrastructure/Migrations
	#@printf "$(OK_COLOR)==> Running static analysis...$(NO_COLOR)\n"
	#@docker run -it --rm -v $(PWD):/app -w /app phpstan/phpstan analyse -c /app/phpstan.neon --level=4 /app/src

tail.log:
	@printf "$(OK_COLOR)==> Tail log file ...$(NO_COLOR)\n"
	@docker-compose exec chassis tail /var/log/chassis/app.log

db.list:
	@printf "$(OK_COLOR)==> Listing migration commands ...$(NO_COLOR)\n"
	@docker-compose exec chassis bin/console list

db.generate:
	@printf "$(OK_COLOR)==> Generating migration ...$(NO_COLOR)\n"
	@docker-compose exec chassis bin/console migrations:generate

db.migrate:
	@printf "$(OK_COLOR)==> Migrating database...$(NO_COLOR)\n"
	@docker-compose exec chassis bin/console migrations:migrate

db.reset:
	@printf "$(OK_COLOR)==> Resetting database...$(NO_COLOR)\n"
	@docker-compose exec chassis bin/console mig:mig first -n && bin/console mig:mig -n

test:
	@printf "$(OK_COLOR)==> Running unit tests...$(NO_COLOR)\n"
	@docker run -it --rm -v $(PWD):/app -w /app --network=chassis_default \
	-e APP_NAME='chassis-test' -e APP_DEBUG='false' \
	averor/docker-phpunit-php-7.1 vendor/bin/phpunit --coverage-text=build/coverage.txt
	@cat $(PWD)/build/coverage.txt
	@printf "\n"
	#@printf "$(OK_COLOR)==> Running integration tests...$(NO_COLOR)\n"
	#@docker-compose exec chassis vendor/bin/behat

.PHONY: all dependencies standards test
