# Developing

- Get the source

    $ git clone https://github.com/codeclimate/php-test-reporter

- Install dependencies

    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install --dev

- Run the tests

    $ ./vendor/bin/phpunit

- Submit PRs to https://github.com/codeclimate/php-test-reporter

*Note*: all changes and fixes must have appropriate test coverage.
