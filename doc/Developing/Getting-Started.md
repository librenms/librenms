# Get ready to contribute to LibreNMS

This document helps you to set up your local environment for
contributions to the LibreNMS project.

## Setting up a development environment

Do not make changes on your production server. A separate environment
for your code changes makes the work easier.

These options are available:

- A Linux computer, VM, or container
- Another directory on your LibreNMS server
- Windows Subsystem for Linux

### Set up your development git clone

1. Obey the [documentation on using git](Using-Git.md)

1. Install development dependencies `./scripts/composer_wrapper.php install`

1. Set the variables in `.env`. These variables include the database
   settings. The MySQL server is local or remote. It can be your
   production database.

    ```dotenv
    APP_ENV=local
    APP_DEBUG=true
    ...
    ```

1. Start a development webserver `./lnms serve`

1. Access the Web UI at <http://localhost:8000>

### Automated testing

LibreNMS uses continuous integration to test the code changes. These
tests reduce the number of bugs. They also keep your contribution
correct in the future. For more information, read the [Validating Code
documentation](Validating-Code.md).

The default database connection for automated testing is `testing`.

To override the database parameters of the unit tests, edit your `.env`
file. These are the defaults from `config/database.php`:

```dotenv
DB_TEST_DRIVER="mysql"   # PDO driver
DB_TEST_HOST="localhost" # hostname or IP address
DB_TEST_PORT=""          # port
DB_TEST_DATABASE="librenms_phpunit_78hunjuybybh" # database
DB_TEST_USERNAME="root"  # username
DB_TEST_PASSWORD=""      # password
DB_TEST_SOCKET=""        # unix socket path
```

### Polling debug output

To see detailed information, run your polling code in debug mode. The
flag `-vv` hides most of the sensitive data. The flag `-vvv` gives the
full debug output.

```bash
lnms device:discover -vv HOSTNAME
lnms device:poll -vv HOSTNAME
```

### Inspecting variables

The `dd()` function shows the content of a variable, such as the data
from an snmpwalk. It dumps one or more variables and then stops the
execution.

```php
dd($variable1, $variable2);
```

### Inspecting web pages

The development dependencies and `APP_DEBUG` enable the [Laravel
Debugbar](https://github.com/barryvdh/laravel-debugbar). You can then
examine the page generation and the errors in your web browser.

### Better code completion in IDEs and editors

These commands generate files for better code completion. The files do
not update automatically. Run the commands again from time to time.

```bash
./lnms ide-helper:generate
./lnms ide-helper:models -N
```

### Emulating devices

[Snmpsim](https://github.com/etingof/snmpsim) captures and emulates
devices. LibreNMS has scripts for the snmprec files. See the [LibreNMS
Snmpsim helpers](https://github.com/librenms/librenms-snmpsim).

### Laravel documentation

The [Laravel documentation](https://laravel.com/docs/) explains much of
the operation of LibreNMS.
