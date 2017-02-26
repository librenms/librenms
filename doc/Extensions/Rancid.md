source: Extensions/Rancid.md
# Rancid integration

Librenms can generate a list of hosts that can be monitored by RANCID.
We assume you have currently a running Rancid, and you just need to create and update the file 'router.db'

### Included Rancid script

To generate the config file (maybe even add a cron to schedule this). We've assumed a few locations for Rancid, the config file you want to call it and where LibreNMS is:

```bash
cd /opt/librenms/scripts/
php ./gen_rancid.php > /the/path/where/is/rancid/core/router.db
```

Sample cron:

```bash
15   0    * * * root cd /opt/librenms/scripts && php ./gen_rancid.php > /the/path/where/is/rancid/core/router.db
```

Now configure LibreNMS (make sure you point dir to your rancid data directory):

```php
$config['rancid_configs']['core'] = '/the/path/where/is/rancid/core';
$config['rancid_ignorecomments'] = 0;
```

After that, you should see some "config" tab on routers that have a rancid update.

