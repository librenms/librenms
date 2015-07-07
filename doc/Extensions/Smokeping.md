# Smokeping integration

We currently have two ways to use Smokeping with LibreNMS, the first is using the included script generator to generate the config for Smokeping. The 
second is to utilise an existing Smokeping setup.

### Included Smokeping script

To use this, please add something similar to your smokeping config file:

```bash
@include /opt/smokeping/etc/librenms.conf
```

Then you need to generate the config file (maybe even add a cron to schedule this in and reload smokeping). We've assumed a few locations for smokeping, the config file you want 
to call it and where LibreNMS is:

```bash
cd /opt/librenms/scripts/
php ./gen_smokeping.php > /opt/smokepgin/etc/librenms.conf
/opt/smokeping/bin/smokeping --reload
```

Sample cron:

```bash
15   0    * * * root cd /opt/librenms/scripts && php ./gen_smokeping.php > /opt/smokepgin/etc/librenms.conf && /opt/smokeping/bin/smokeping --reload >> /dev/null 2>&1
```

Now configure LibreNMS (make sure you point dir to your smokeping data directory:

```php
$config['smokeping']['dir'] = '/opt/smokeping/data';
$config['smokeping']['integration'] = true;
```

### Standard Smokeping

This is quite simple, just point your dir at the smokeping data directory - please be aware that all RRD files need to be within this dir and NOT sub dirs:

```php
$config['smokeping']['dir'] = '/opt/smokeping/data';
$config['own_hostname']
```

You should now see a new tab in your device page called ping.
