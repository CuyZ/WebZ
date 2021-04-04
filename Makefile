help:
	@echo "Please use \`make <target>' where <target> is one of"
	@echo "  start-server                   to start the test server"
	@echo "  stop-server                    to stop the test server"
	@echo "  test                           to perform tests."
	@echo "  test-unit                      to perform unit tests."
	@echo "  test-integration               to perform integration tests."
	@echo "  coverage                       to perform tests with code coverage."
	@echo "  static                         to run phpstan and psalm on the codebase"

install:
	composer install

start-server: stop-server
	php tests/bin/server.php &> /dev/null &

stop-server:
	@PID=$(shell ps axo pid,command \
	  | grep 'tests/bin/server.php' \
	  | grep -v grep \
	  | cut -f 1 -d " "\
	) && [ -n "$$PID" ] && kill $$PID || true

test: start-server
	php vendor/bin/phpunit --testsuite=unit,integration
	$(MAKE) stop-server

test-unit:
	php vendor/bin/phpunit --testsuite=unit

test-integration: start-server
	php vendor/bin/phpunit --testsuite=integration
	$(MAKE) stop-server

coverage:
	php -d xdebug.mode=coverage vendor/bin/phpunit --coverage-text --testsuite=unit

phpstan:
	php vendor/bin/phpstan analyse

psalm:
	php vendor/bin/psalm

static:
	php vendor/bin/phpstan analyse
	php vendor/bin/psalm

.PHONY: install start-server stop-server test test-unit test-integration coverage phpstan psalm static
