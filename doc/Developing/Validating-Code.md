source: Developing/Validating-Code.md

As part of the pull request process with GitHub we run some automated build tests to ensure that 
the code is error free, standards [compliant](http://docs.librenms.org/Developing/Code-Guidelines/)
and our test suite builds successfully.

Rather than submit a pull request and wait for the results, you can run these checks yourself to ensure 
a more seamless merge.

> All of these commands should be run from within the librenms directory and can be run as the librenms user 
unless otherwise noted.

### Syntax checks
If you run PHP 5.3, 5.4 or HHVM please run:

`find . -regextype posix-extended -regex "\./(lib/influxdb-php|vendor)" -prune -o -name "*.php" -print0 | xargs -0 -n1 -P8 php -l | grep -v '^No syntax errors detected' ; test $? -eq 1`

If you run PHP 5.5 or above then please run:

`find . -path './vendor' -prune -o -name "*.php" -print0 | xargs -0 -n1 -P8 php -l | grep -v '^No syntax errors detected' ; test $? -eq 1`

### PSR-2 Compliance
This will ensure that your code standards match PSR-2 which is what our code base follows. To run this you need to install `phpcs`:

`sudo pear install PHP_CodeSniffer`

You can then run:

`phpcs -n -p --colors --extensions=php --standard=PSR2 --ignore=html/lib/* --ignore=html/plugins/ html`

### Test suite
For this you will need `phpunit`, this can be installed using the following commands:

```bash
wget https://phar.phpunit.de/phpunit.phar
chmod +x phpunit.phar
sudo mv phpunit.phar /usr/local/bin/phpunit
```

Now you can run the tests by entering `phpunit`.

If all of the above tests complete ok then please submit your pull request. 
