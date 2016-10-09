source: General/Updating.md
## Updating your install ##

If you would like to perform a manual update
then you can do this by running the following command as the **librenms** user:

`./daily.sh`

This will update both the core LibreNMS files but also update the database
structure if updates are available.

## Configuring the update channel ##
LibreNMS follows the master branch on github for daily updates.
You can change to the monthly releases by setting:

`$config['update_channel'] = 'release';`

## Disabling automatic updates ##
LibreNMS by default performs updates on a daily basis. This can be disabled
by ensuring:

`$config['update'] = 0;`

is no longer commented out. 
