help:
	@echo "Please use \`make <target>' where <target> is one of"
	@echo "  start-server                   to start the test server"
	@echo "  stop-server                    to stop the test server"
	@echo "  test                           to perform tests."
	@echo "  test-unit                      to perform unit tests."
	@echo "  test-integration               to perform integration tests."
	@echo "  coverage                       to perform tests with code coverage."
	@echo "  coverage-unit                  to perform unit tests with code coverage."
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
	php vendor/bin/pest --group=unit,integration
	$(MAKE) stop-server

test-unit:
	php vendor/bin/pest --group=unit

test-integration: start-server
	php vendor/bin/pest --group=integration
	$(MAKE) stop-server

# These tests are a bit of a hack so they need to be executed on their own
test-platform:
	php vendor/bin/pest tests/Platform/SoapTest.php
	php vendor/bin/pest tests/Platform/HttpTest.php

coverage: start-server
	php vendor/bin/pest --coverage --group=unit,integration
	$(MAKE) stop-server

coverage-unit:
	php vendor/bin/pest --group=unit --coverage

coverage-integration:
	php vendor/bin/pest --group=integration --coverage

static-phpstan:
	php vendor/bin/phpstan analyse

static-psalm:
	php vendor/bin/psalm

static:
	php vendor/bin/phpstan analyse
	php vendor/bin/psalm
