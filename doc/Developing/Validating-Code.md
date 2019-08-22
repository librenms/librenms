source: Developing/Validating-Code.md
path: blob/master/doc/

#### Validating Code

As part of the pull request process with GitHub we run some automated
build tests to ensure that  the code is error free, standards [compliant](http://docs.librenms.org/Developing/Code-Guidelines/)
and our test suite builds successfully.

Rather than submit a pull request and wait for the results, you can
run these checks yourself to ensure  a more seamless merge.

> All of these commands should be run from within the librenms
> directory and can be run as the librenms user  unless otherwise noted.

Install composer (you can skip this if composer is already installed).

`curl -sS https://getcomposer.org/installer | php`

Composer will now be installed into /opt/librenms/composer.phar.

Now install the dependencies we require:

`./composer.phar install`

Once composer is installed you can now run the code validation script:

`./scripts/pre-commit.php`

If you see `Tests ok, submit away :)` then all is well. If you see
other output then it should contain  what you need to resolve the issues and re-test.

#### Git Hooks

Git has a hook system which you can use to trigger checks at various
stages. Utilising the `pre-commit.php`  you can make this part of your
commit process. You can do this in two ways:

1. If you already make use of `.git/hooks/pre-commit` then you can add:

./scripts/pre-commit.php

to be excecuted when you run `git commit`.

2. You can symlink the `pre-commit.php` file so it's executed directly
   on a `git commit`:

`ln -s /opt/librenms/scripts/pre-commit.php /opt/librenms/.git/hooks/pre-commit`
