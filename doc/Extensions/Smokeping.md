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
php ./gen_smokeping.php > /opt/smokeping/etc/librenms.conf
/opt/smokeping/bin/smokeping --reload
```

Sample cron:

```bash
15   0    * * * root cd /opt/librenms/scripts && php ./gen_smokeping.php > /opt/smokeping/etc/librenms.conf && /opt/smokeping/bin/smokeping --reload >> /dev/null 2>&1
```

Now configure LibreNMS (make sure you point dir to your smokeping data directory:

```php
$config['smokeping']['dir'] = '/opt/smokeping/data';
$config['smokeping']['pings'] = 20;		// should be equal to "pings" in your smokeping config
$config['smokeping']['integration'] = true;
```

### Standard Smokeping

This is quite simple, just point your dir at the smokeping data directory - please be aware that all RRD files need to be within this dir and NOT sub dirs:

```php
$config['smokeping']['dir'] = '/opt/smokeping/data';
$config['smokeping']['pings'] = 20;		// should be equal to "pings" in your smokeping config
$config['own_hostname']
```

You should now see a new tab in your device page called ping.




### Install and integrate Smokeping [Debian/Ubuntu] ###

> This guide assumes you have already <a href="http://docs.librenms.org/Installation/Installation-(Debian-Ubuntu)/">installed librenms</a>, and you installed apache2 in the process. Tested with Ubuntu 14.04 and Apache 2.4.

Nearly everything we do will require root, and at one point we'll encounter a problem if we just use sudo, so we'll just switch to root at the beginning...

```bash
sudo su -
```

### Install Smokeping ###

```bash
apt-get install smokeping
```

At the end of installation, you may have gotten this error: `ERROR: /etc/smokeping/config.d/pathnames, line 1: File '/usr/sbin/sendmail' does not exist`

If so, just edit smokeping's pathnames.

```bash
nano /etc/smokeping/config.d/pathnames
```

Comment out the first line:

```bash
#sendmail = /usr/sbin/sendmail
```

Exit and save.

Check if the smokeping config file was created for apache2:

```bash
ls /etc/apache2/conf-available/
```

If you don't see `smokeping.conf` listed, you'll need to create a symlink for it:

```bash
ln -s /etc/smokeping/apache2.conf /etc/apache2/conf-available/smokeping.conf
```

Edit the smokeping config so smokeping knows the hostname it's running on:

```bash
nano /etc/smokeping/config.d/General
```

Change the `cgiurl` value to `http://yourhost/cgi-bin/smokeping.cgi`
Modify any other values you wish, then exit and save.

### LibreNMS integration ###

So far this is a relatively normal Smokeping installation; next we'll set up the LibreNMS integration.

Generate the configuration file so Smokeping knows the hosts you have set up for monitoring in LibreNMS.

```bash
cd /opt/librenms/scripts/
(echo "+ LibreNMS"; php ./gen_smokeping.php) > /etc/smokeping/config.d/librenms.conf
```

Add a cron job so as you add or remove hosts in librenms they'll get updated with Smokeping.

```bash
crontab -e
```

Add the example cron below; it's set to run daily at 02:05

```bash
05 02 * * * root cd /opt/librenms/scripts && (echo "+ LibreNMS"; php ./gen_smokeping.php) > /etc/smokeping/config.d/librenms.conf && service smokeping reload >> /dev/null
```

Exit and save.

Include `librenms.conf` in smokeping's config:
```bash
nano /etc/smokeping/config
```

Add the following line at the end:

```bash
@include /etc/smokeping/config.d/librenms.conf
```

Exit and save.

### Configure LibreNMS ###

```bash
nano /opt/librenms/config.php
```

Scroll to the bottom, and paste in the following:

```bash
$config['smokeping']['dir'] = '/var/lib/smokeping';
$config['smokeping']['pings'] = 20;		// should be equal to "pings" in your smokeping config
$config['smokeping']['integration'] = true;
```

Exit and save.

Run the following commands:
```bash
a2enconf smokeping
a2enmod cgid
service apache2 restart
service smokeping restart
```

Return to your normal user shell

```bash
exit
```

Done! You should be able to load the Smokeping web interface at `http://yourhost/cgi-bin/smokeping.cgi`
In LibreNMS, a Ping tab should also appear.
