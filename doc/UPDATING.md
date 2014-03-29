## Updating your install ##

LibreNMS by default performs updates on a daily basis. This can be disabled by ensuring:

	$config['update'] = 0;

Is no longer commented out. If you would like to perform a manual update then you can do this by running the following commands:

	git pull --no-edit --quiet
	php includes/sql-schema/update.php

This will update both the core LibreNMS files but also update the database structure if updates are available.
