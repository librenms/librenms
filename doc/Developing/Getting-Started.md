# Get ready to contribute to LibreNMS

This document is intended to help you get your local environment set
up to contribute code to the LibreNMS project.

## Setting up a development environment

When starting to develop, it may be tempting to just make changes on
your production server, but that will make things harder for you.
Taking a little time to set up somewhere to work on code changes can
really help.

Possible options:

- A Linux computer, VM, or container
- Another directory on your LibreNMS server
- Windows Subsystem for Linux

### Set up your development git clone

1. Follow the [documentation on using git](Using-Git.md)

1. Install development dependencies `./scripts/composer_wrapper.php install`

1. Set variables in .env, including database settings.  Which could be
   a local or remote MySQL server including your production DB.

```dotenv
APP_ENV=local
APP_DEBUG=true
...
```

1. Start a development webserver `./lnms serve`

1. Access the Web UI at <http://localhost:8000>

### Automated testing

LibreNMS uses continuous integration to test code changes to help
reduce bugs.  This also helps guarantee the changes you contribute
won't be broken in the future. You can find out more in our [Validating Code Documentation](Validating-Code.md)

The default database connection for automated testing is `testing`.

To override the database parameters for unit tests, configure your
`.env` file accordingly. The defaults (from `config/database.php`)
are:

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

You can see detailed info by running your polling code in debug
mode by adding a `-d` flag.

```bash
./discovery.php -d -h HOSTNAME
./poller.php -d -h HOSTNAME
```

### Inspecting variables

Sometimes you want to find out what a variable contains (such as the
data return from an snmpwalk). You can dump one or more variables and
halt execution with the dd() function.

```php
dd($variable1, $variable2);
```

### Inspecting web pages

Installing the development dependencies and setting APP_DEBUG enables
the [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)
This will allow you to inspect page generation and errors right in
your web browser.

### Better code completion in IDEs and editors

You can generate some files to improve code completion. (These file
are not updated automatically, so you may need to re-run these command
periodically)

```bash
./lnms ide-helper:generate
./lnms ide-helper:models -N
```

### Emulating devices

You can capture and emulate devices using
[Snmpsim](https://github.com/etingof/snmpsim).  LibreNMS has a set of
scripts to make it easier to work with snmprec files.
[LibreNMS Snmpsim helpers](https://github.com/librenms/librenms-snmpsim)

### Laravel documentation

You can find a lot of how LibreNMS works by following the [Laravel Documentation](https://laravel.com/docs/)
