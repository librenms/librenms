source: General/Updating.md
## Updating your install ##

If you would like to perform a manual update
then you can do this by running the following command as the **librenms** user:

`./daily.sh`

This will update both the core LibreNMS files but also update the database
structure if updates are available.

#### Advanced users
If you absolutely must update manually then you can do so by running the following commands:
```bash
cd /opt/librenms
git pull
php includes/sql-schema/update.php
```

## Configuring the update channel ##
LibreNMS follows the master branch on github for daily updates.

#### Stable branch
You can change to the stable monthly branch by setting:

`$config['update_channel'] = 'release';`

> Choose this branch if you want to have a stable release 

#### Development branch
You can change to the development branch by setting:

`$config['update_channel'] = 'master';`

> Choose this branch if you want the latest features at the cost that sometimes bugs are inadvertently introduced. 

## Disabling automatic updates ##
LibreNMS by default performs updates on a daily basis. This can be disabled by setting:

`$config['update'] = 0;`
