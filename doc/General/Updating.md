# Updating an Install

By default, LibreNMS is set to automatically update once a day at  00:19 hours.
If you have disabled this feature then you can perform a manual update.

## Manual update

If you would like to perform a manual update then you can do this by
running the following command as the `librenms` user:

```bash
./daily.sh
```

This will update the core LibreNMS files and also update the database
structure if any are available.

## Advanced users

If you absolutely must update manually without using `./daily.sh` then
you can do so by running the following commands:

```bash
cd /opt/librenms
git pull
rm bootstrap/cache/*.php
./scripts/composer_wrapper.php install --no-dev
./lnms migrate
./validate.php
```

## Disabling automatic updates

LibreNMS by default performs updates on a daily basis.
This can be disabled in the WebUI:

!!! warning
    You should never remove daily.sh from the cronjob!
    This does database cleanup and other processes in addition to updating.

!!! setting "system/updates"
    ```bash
    lnms config:set update false
    ```
