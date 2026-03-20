# Validating Code

As part of the pull request process with GitHub we run some automated
build tests to ensure that the code is error free, standards compliant
and our test suite builds successfully.

Rather than submit a pull request and wait for the results, you can
run these checks yourself to ensure a more seamless merge.

!!! note ""
    All of these commands should be run from within the librenms
    directory and can be run as the `librenms` user unless otherwise noted.

## Install composer 

Composer is a dependency manager for PHP, we use it to manage our dependencies and to run our code validation checks.

Composer requires PHP and PHP extensions to be installed, you can install it with the following commands:

### installing PHP extensions

=== "Debian/Ubuntu"

    ```bash
    sudo apt update
    sudo apt install php8.4-curl php8.4-gd php8.4-xml php8.4-zip
    ```

=== "RHEL/CentOS"

    ```bash
    sudo dnf install php-curl php-gd php-xml php-zip
    ```

### installing composer
Then we can install composer itself:

```bash
curl -sS https://getcomposer.org/installer | php
```

You can check if the PHP extensions are installed with:

```bash
./composer.phar check 
```

Composer will now be installed into `/opt/librenms/composer.phar`.

### installing dependencies and running checks
Now install the dependencies we require:

```bash
./composer.phar install
```

Once composer is installed you can now run the code validation script:

```bash
./lnms dev:check
```

If you see `Tests ok, submit away :)` then all is well. If you see
other output then it should contain what you need to resolve the issues and re-test.

## Git Hooks

Git has a hook system which you can use to trigger checks at various
stages. Utilising the `./lnms dev:check` you can make this part of your
commit process.

Add `./lnms dev:check` to your `.git/hooks/pre-commit`:

```bash
echo "/opt/librenms/lnms dev:check" >> /opt/librenms/.git/hooks/pre-commit
chmod +x /opt/librenms/.git/hooks/pre-commit
```
