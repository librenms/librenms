## Updating your install ##

LibreNMS by default performs updates on a daily basis. This can be disabled
by ensuring:

	$config['update'] = 0;

is no longer commented out. If you would like to perform a manual update
then you can do this by running the following command:

	./daily.sh

This will update both the core LibreNMS files but also update the database
structure if updates are available.
