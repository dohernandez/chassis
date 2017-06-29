.PHONY: all dependencies standards
NO_COLOR=\033[0m
OK_COLOR=\033[32;01m
WARN_COLOR=\033[33;01m

all: dependencies

dependencies:
	@printf "$(OK_COLOR)==> Installing dependencies...$(NO_COLOR)\n"
	@docker run -it --rm -v $(PWD):/app -w /app prooph/composer:7.1 update --prefer-dist --no-interaction --optimize-autoloader --no-progress

autoload.dependency:
	@printf "$(OK_COLOR)==> Dump dependencies ...$(NO_COLOR)\n"
	@docker run -it --rm -v $(PWD):/app -w /app prooph/composer:7.1 dump-autoload --optimize

tail.log:
	@printf "$(OK_COLOR)==> Tail log file ...$(NO_COLOR)\n"
	@docker-compose exec chassis tail /var/log/chassis/app.log

standards:
	@printf "$(OK_COLOR)==> Checking code standards...$(NO_COLOR)\n"
	@docker run -it --rm -v $(PWD):/app -w /app rcrosby256/php-cs-fixer fix --dry-run --diff
	@printf "$(OK_COLOR)==> Checking for code mess...$(NO_COLOR)\n"
	@docker run -it --rm -v $(PWD):/workspace shavenking/docker-phpmd src text ruleset.xml --suffixes php --exclude src/Infrastructure/Migrations
	@printf "$(OK_COLOR)==> Running static analysis...$(NO_COLOR)\n"
	@docker run -it --rm -v $(PWD):/app -w /app phpstan/phpstan analyse -c /app/phpstan.neon --level=4 /app/src

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
	@docker run -it --rm -v $(PWD):/app -w /app --network=chassis_default averor/docker-phpunit-php-7.1 vendor/bin/phpunit --coverage-text=tests/coverage.txt
	@cat $(PWD)/tests/coverage.txt
	@printf "\n"
	#@printf "$(OK_COLOR)==> Running integration tests...$(NO_COLOR)\n"
	#@docker-compose exec chassis vendor/bin/behat
