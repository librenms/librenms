source: Extensions/Rancid.md
path: blob/master/doc/

# Rancid integration

Librenms can generate a list of hosts that can be monitored by
RANCID. We assume you have currently a running Rancid, and you just
need to create and update the file 'router.db'

# Included Rancid script

To generate the config file (maybe even add a cron to schedule
this). We've assumed a few locations for Rancid, the config file you
want to call it and where LibreNMS is:

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

# Ubuntu Rancid Install

The options shown below also contains the default values.

> NOTE - This is Only for Ubuntu 16.04 at this time, and may not work on other distros!

`sudo apt-get install rancid subversion`

Edit Rancid config file to use subversion or git instead of default
cvs, and adds a group:
`sudo vi /etc/rancid/rancid.conf`

`LIST_OF_GROUPS="librenms"`

Now change these two lines:

```
CVSROOT=$BASEDIR/CVS; export CVSROOT
RCSSYS=cvs; export RCSSYS
```

to:

```
CVSROOT=$BASEDIR/SVN; export CVSROOT
RCSSYS=svn; export RCSSYS
```

NOTE - This only creates 1 group! You can of course make more when you
get the hang of it, this is just a basic 'Need it to work" deal.

`sudo su -c /var/lib/rancid/bin/rancid-cvs -s /bin/bash -l rancid`
> NOTE - do NOT change cvs to svn here! Leave command as is!

Get a list of devices from Librenms you can pull configs from:

```
cd /opt/librenms/scripts
sudo ./gen_rancid.php
```

Copy the output. Replace all ":" with ";" example:

```
alphcr1:cisco:up will change to:
alphcr1;cisco;up

```

copy and past results into the below file:
`sudo vi /var/lib/rancid/librenms/router.db`

NOTE - This ONLY applies to newer RANCID versions and Linux
distros. Older versions will need to retain the : and not the ;

Create/edit rancids login file:

`sudo vi /var/lib/rancid/.cloginrc`

Add following at minimum:

```
add user * <your username here>
add password * <your password here>
add method * ssh
add noenable * {1}                         ******This disables the enable when using radius etc *******
```

Grant permissions for rancid:

```
sudo chown rancid /var/lib/rancid/.cloginrc
sudo chmod 600 /var/lib/rancid/.cloginrc
```

Test config:
`sudo /usr/lib/rancid/bin/clogin -f /var/lib/rancid/.cloginrc <device hostname>`

NOTE: IF you run into a 'diffie-hellmen' kind of error, then it is
because your Linux distro is using newer encyryprtion methods
etc. This is basically just letting you know that the device you
tested on is running an outdated encryption type. I recommend updating
downstream device if able.  If not, the following should fix:

`sudo vi /etc/ssh/ssh_config`

Add:

`KexAlgorithms diffie-hellman-group1-sha1`

Re-try logging into your device again

Upon success, run rancid:

`sudo su -c /var/lib/rancid/bin/rancid-run -s /bin/bash -l rancid`

Ensure your configs pulled:

```sudo su - rancid
cd librenms/configs/
ls
```

Make sure your config files are there :-)

```
sudo usermod -a -G rancid www-data
cd /opt/librenms/
```

Add Rancid into LibreNMS config.php:

```php
### Rancid
$config['rancid_configs'][]             = '/var/lib/rancid/librenms/configs/';
$config['rancid_repo_type']             = 'svn';  //'svn' or 'git'
$config['rancid_ignorecomments']        = 0;
```

Now restart apache
`sudo /etc/init.d/apache2 restart`
