# vim: tabstop=4:softtabstop=4:shiftwidth=4:noexpandtab

help:

	@echo "Usual targets:"
	@echo "  test - run test suites"
	@echo ""
	@echo "Other targets:"
	@echo "  install-composer - install composer"
	@echo "  install-dependencies - install/update all vendor libraries using composer"
	@echo "  install-dev-dependencies - install/update all vendor libraries necessary for development"
	@echo ""
	@exit 0

test:

	@vendor/bin/phpunit

install-composer:

	@if [ ! -d ./bin ]; then mkdir bin; fi
	@if [ ! -f ./bin/composer.phar ]; then curl -s http://getcomposer.org/installer | php -n -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin/; fi

install-dependencies:

	@make install-composer
	@php -n -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" ./bin/composer.phar -- update

install-dev-dependencies:

	@make install-composer
	@php -n -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" ./bin/composer.phar update --dev
	
.PHONY: test help

