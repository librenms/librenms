# Environment Variables

You can set some LibreNMS settings in the environment or in the `.env`
file.

## Database

Set these variables for the connection to the database. The default
values are below.

```dotenv
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=librenms
DB_USERNAME=librenms
DB_PASSWORD=
DB_SOCKET=
```

## Trusted Reverse Proxies

This variable holds a comma separated list of trusted reverse proxy IP
addresses or CIDR ranges.

The default value is `127.0.0.1`. This value permits reverse proxies
only on the localhost.

Do not use these two values, because they are not secure:
`'*'` trusts any proxy.
`'**'` trusts any proxy in the chain.

```dotenv
APP_TRUSTED_PROXIES=192.168.1.0/24,192.167.8.20
```

## Base url

This variable sets the base URL for the generated URLs.

Signed graph URLs for alerting need this variable. A reverse proxy with
a subdirectory can also need it.

LibreNMS usually builds correct URLs. This is true when your proxy
variables are correct.

```dotenv
APP_URL=http://librenms/
```

## User / Group

These variables set the user and the group for LibreNMS.
If you do not set the group, the group takes the value of the user.

```dotenv
LIBRENMS_USER=librenms
LIBRENMS_GROUP=librenms
```

## Composer packages

`lnms plugin:add` records the packages you add in `composer.plugins.json`
in the install directory. This variable moves that file, for example to
a persistent volume.

```dotenv
LIBRENMS_COMPOSER_PLUGINS=/data/composer.plugins.json
```

`lnms plugin:sync` installs all packages in that file again.

The plugin commands also honor the `COMPOSER` and `COMPOSER_VENDOR_DIR`
variables of Composer. Set them in the environment to leave the
`composer.json`, `composer.lock` and `vendor` of the install directory
unchanged. If the file in `COMPOSER` does not exist, it is created from
the `composer.json` and `composer.lock` of the install directory.
The directory in `COMPOSER_VENDOR_DIR` must already contain the installed
packages, for example a copy of `vendor`. LibreNMS loads `vendor` from
the install directory, so link it to that directory.

## Debug

This variable increases the information that LibreNMS shows for an error.

> WARNING: Do not leave this variable enabled. It can leak information.

```dotenv
APP_DEBUG=true
```
