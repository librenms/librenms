# Updating an Install

By default, LibreNMS updates one time each day at 00:19 hours.
If you disabled this feature, you can do a manual update.

## Manual update

To do a manual update, run this command as the `librenms` user:

```bash
./daily.sh
```

This command updates the core LibreNMS files. It also updates the
database structure when a new structure is available.

## Advanced users

If you must update without `./daily.sh`, run these commands:

```bash
cd /opt/librenms
git pull
rm bootstrap/cache/*.php
./scripts/composer_wrapper.php install --no-dev
./lnms migrate
./validate.php
```

## Disabling automatic updates

By default, LibreNMS updates each day.
You can disable the updates in the web interface.

!!! warning
    Do not remove `daily.sh` from the cronjob.
    This script also does database cleanup and other processes.

!!! setting "system/updates"
    ```bash
    lnms config:set update false
    ```

## Updating on set days

You can configure LibreNMS to update only on set days. This configuration is an
array. The array is empty by default.

!!! setting "system/updates"
    ```bash
    lnms config:get update_on_days
    ```
    ```bash
    lnms config:set update_on_days.+ "monday"
    ```
