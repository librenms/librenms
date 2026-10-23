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

## Testing pull requests

You can apply a pull request from GitHub to your install to test it before it is
merged. Run these commands as the `librenms` user:

```bash
lnms patch:apply 12345
```

You can give more than one pull request number, or the URL of the pull request.
To update to the newest version of a pull request, run the same command again.

To see which pull requests are applied, and if they have been updated or merged:

```bash
lnms patch:list
```

When you are done testing, remove the pull request:

```bash
lnms patch:remove 12345
```

This command removes only the changes from the pull request. Other local changes
are not touched. Run `lnms patch:remove` without a pull request number to remove
all of them, you are asked to confirm first.

If the pull request added database migrations that have run, you are asked if
they should be rolled back. The default is to keep them. Rolling back runs the
migration's `down()` method, which can delete data and is not guaranteed to
restore your database to how it was before you applied the pull request.

!!! danger
    `lnms patch:remove --reset` discards ALL local changes, including changes you
    made by hand, and deletes untracked files in the LibreNMS code directories.
    It is not needed to remove test patches. It lists the files it will discard
    and asks you to confirm before it changes anything.

Applied pull requests are never removed automatically. They stay applied when
LibreNMS updates. If an update changes files that an applied pull request also
changed, LibreNMS is not updated. It stays on the current version until you
remove the pull request, or apply its newest version. You get a notification in
the web interface when this happens, including when the pull request has been
merged.

In Docker, the applied pull requests are lost when the container is recreated
from a new image.
