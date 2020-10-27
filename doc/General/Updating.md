source: General/Updating.md
path: blob/master/doc/

By default, LibreNMS is set to automatically update. If you have
disabled this feature then you can perform a manual update.

# Manual update

If you would like to perform a manual update then you can do this by
running the following command as the `librenms` user:

`./daily.sh`

This will update both the core LibreNMS files but also update the database
structure if updates are available.

# Advanced users

If you absolutely must update manually without using `./daily.sh` then
you can do so by running the following commands:

```bash
cd /opt/librenms
git pull
./scripts/composer_wrapper.php install --no-dev
php includes/sql-schema/update.php
./validate.php
```

You should continue to run daily.sh.  This does database cleanup and
other processes in addition to updating. You can disable the daily.sh
update process as described below.

# Disabling automatic updates

LibreNMS by default performs updates on a daily basis. This can be disabled by setting:

`$config['update'] = 0;`
