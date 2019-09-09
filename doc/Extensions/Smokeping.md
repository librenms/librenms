source: Extensions/Smokeping.md
path: blob/master/doc/

# Smokeping integration

[SmokePing](https://oss.oetiker.ch/smokeping/) is a tool which lets us
keep track of network latency, and visualise this through RRD graphs.

LibreNMS has support for both new and pre-existing SmokePing installations.

For new installations, we can use the included
`scripts/gen_smokeping.php` script to generate a Smokeping config file.

## New Smokeping installation

### Install and integrate Smokeping - Debian/Ubuntu

This guide assumes you have already [installed
librenms](http://docs.librenms.org/Installation/Installing-LibreNMS/),
and is working with either **Apache** or
**nginx**.

Note: You may need to install `fcgiwrap` as well (at least with `nginx`).

### Install Smokeping

```bash
sudo apt update && sudo apt install smokeping
```

## Configure SmokePing

Smokeping has several configuration files. By default, these are
located in `/etc/smokeping/config.d/`

Edit the `General` configuration file's **Owner** and **contact**, and
**cgiurl hostname** details:

```bash
nano /etc/smokeping/config.d/General
owner    = LibreNMS-Admin
contact  = admin@ACME.xxx
cgiurl   = http://yourlibrenms/cgi-bin/smokeping.cgi
```

### Configure Smokeping to use LibreNMS list of nodes

Add the following line to `/etc/smokeping/config` config file:

```bash
@include /etc/smokeping/config.d/librenms.conf
```

We will generate the conf file in the next step.

### Generate LibreNMS list of Smokeping Nodes

LibreNMS comes equipped with a script which exports our list of nodes
from LibreNMS into a configuration file in the format required by
Smokeping.

To generate the config file once:

```bash
(echo "+ LibreNMS"; php -f /opt/librenms/scripts/gen_smokeping.php) | sudo tee /etc/smokeping/config.d/librenms.conf
```

**However**, it is more desirable to set up a cron job which
regenerates our list of nodes and adds these into Smokeping. You can
add the following to the end of your librenms cron job, e.g. `nano /etc/cron.d/librenms`

**Ubuntu 16.04** Sample cron (will run daily at 00:05) :

```bash
05  00    * * *   root (echo "+ LibreNMS"; php -f /opt/librenms/scripts/gen_smokeping.php) > /etc/smokeping/config.d/librenms.conf && systemctl reload smokeping.service >> /dev/null 2>&1
```

**Ubuntu 14.04** Sample cron (will run daily at 00:05):

```bash
05  00    * * * root (echo "+ LibreNMS"; php -f /opt/librenms/scripts/gen_smokeping.php) > /opt/smokeping/etc/librenms.conf && /opt/smokeping/bin/smokeping --reload >> /dev/null 2>&1
```

**Why echo "+ LibreNMS" ?**

This is in the cron job because the `gen_smokeping.php` script contains

```
menu = Top
title = Network Latency Grapher
```

Which can cause Smokeping to not start. `echo "+ LibreNMS"` prepends
this in our smokeping config file. We could remove the above from the
gen_smokeping script, however this may cause issues with LibreNMS
failing to update with `daily.sh` due config files being modified.

## Configure LibreNMS

Edit `/opt/librenms/config.php` and add the following:

**Note:** Make sure you point dir to the correct Smokeping data directory:

```php
$config['smokeping']['dir'] = '/var/lib/smokeping'; // Ubuntu 16.04 and newer Location
#$config['smokeping']['dir'] = '/opt/smokeping/data';
$config['smokeping']['pings'] = 20;    // should be equal to "pings" in your smokeping config
$config['smokeping']['integration'] = true;
```

## Configure web server

This section covers the required configuration for your web server of
choice. This covers the required configuration for either Apache or Nginx.

### Apache Configuration

Smokeping should automatically install an Apache config file in
`/etc/apache2/conf-available/`. Verify this using :

```bash
librenms@librenms:~/scripts$ ls /etc/apache2/conf-available/ | grep smokeping
smokeping.conf
```

If you don't see `smokeping.conf` listed, you'll need to create a symlink for it:

```bash
ln -s /etc/smokeping/apache2.conf /etc/apache2/conf-available/smokeping.conf
```

After creating the symlink, restart Apache with `sudo systemctl apache2 restart`

You should be able to load the Smokeping web interface at `http://yourhost/cgi-bin/smokeping.cgi`

### Nginx Configuration

This section assumes you have configured LibreNMS with Nginx as
specified in [Configure Nginx](https://docs.librenms.org/Installation/Installation-Ubuntu-1804-Nginx/).

Add the following configuration to your `/etc/nginx/conf.d/librenms` config file.

The following will configure Nginx to respond to `http://yourlibrenms/smokeping`:

```
#Browsing to `http://librenms.xxx/smokeping/` should bring up the smokeping web interface

 location = /smokeping/ {
        fastcgi_intercept_errors on;

        fastcgi_param   SCRIPT_FILENAME         /usr/lib/cgi-bin/smokeping.cgi;
        fastcgi_param   QUERY_STRING            $query_string;
        fastcgi_param   REQUEST_METHOD          $request_method;
        fastcgi_param   CONTENT_TYPE            $content_type;
        fastcgi_param   CONTENT_LENGTH          $content_length;
        fastcgi_param   REQUEST_URI             $request_uri;
        fastcgi_param   DOCUMENT_URI            $document_uri;
        fastcgi_param   DOCUMENT_ROOT           $document_root;
        fastcgi_param   SERVER_PROTOCOL         $server_protocol;
        fastcgi_param   GATEWAY_INTERFACE       CGI/1.1;
        fastcgi_param   SERVER_SOFTWARE         nginx/$nginx_version;
        fastcgi_param   REMOTE_ADDR             $remote_addr;
        fastcgi_param   REMOTE_PORT             $remote_port;
        fastcgi_param   SERVER_ADDR             $server_addr;
        fastcgi_param   SERVER_PORT             $server_port;
        fastcgi_param   SERVER_NAME             $server_name;
        fastcgi_param   HTTPS                   $https if_not_empty;

        fastcgi_pass unix:/var/run/fcgiwrap.socket;
}

        location ^~ /smokeping/ {
                alias /usr/share/smokeping/www/;
                index smokeping.cgi;
                gzip off;
        }
```

After saving the config file, verify your Nginx config file syntax is
OK with `sudo nginx -t`, then restart Nginx with `sudo systemctl restart nginx`

You should be able to load the Smokeping web interface at `http://yourhost/smokeping`

#### Nginx Password Authentification

You can use the purpose-made htpasswd utility included in the
apache2-utils package (Nginx password files use the same format as
Apache). You can install it on Ubuntu with

```
apt install apache2-utils
```

After that you need to create password for your user

```
htpasswd -c /etc/nginx/.htpasswd USER
```

You can verify your user and password with

```
cat /etc/nginx/.htpasswd
```

Then you just need to add to your config `auth_basic` parameters

```
        location ^~ /smokeping/ {
                alias /usr/share/smokeping/www/;
                index smokeping.cgi;
                gzip off;
                auth_basic "Private Property";
                auth_basic_user_file /etc/nginx/.htpasswd
        }
```

### Start SmokePing

Use the below commands to start and verify smokeping is running.

**Ubuntu 14.04:**  `sudo service smokeping start`

Verify: `sudo service smokeping status`

**Ubuntu 16.04 and newer:**  `sudo systemctl start smokeping`

Verify: `sudo systemctl status smokeping`

## Verify in LibreNMS

Within LibreNMS, you should now have a new device sub-tab called Ping

--------------
# Pre-Existing Smokeping Installation

The following section covers the requirements for an existing
SmokePing installation. The primary difference is this section does
not cover using the LibreNMS Smokeping config script, and assumes an
existing Smokeping server is set up and working correctly.

In terms of configuration, simply add the location of where smokeping
data such as RRD files are stored. If this is on a separate server,
ensure there is a mount point reachable, along with the server's hostname.

**Note:** The location should be the RRD root folder, NOT the
sub-directory such as network.

```php
$config['smokeping']['dir'] = '/var/lib/smokeping'; // Ubuntu 16.04 and newer Location
#$config['smokeping']['dir'] = '/opt/smokeping/data';
$config['smokeping']['pings'] = 20;    // should be equal to "pings" in your smokeping config
$config['smokeping']['integration'] = true;
```

You should now see a new tab in your device page called ping.

# Issues

## `ERROR: /etc/smokeping/config.d/pathnames, line 1: File '/usr/sbin/sendmail' does not exist`

If you got this error at the end of the installation, simply edit
smokeping's config file like so:

```diff
nano /etc/smokeping/config.d/pathnames

-sendmail = /usr/sbin/sendmail
+#sendmail = /usr/sbin/sendmail
```

## Smokeping and RRDCached

If you are using the standard smokeping data dir
(`/etc/smokeping/data`) then you may need to alter the rrdcached
config slightly.

In the standard configuration the -B argument may have been used to
restrict rrdcached to read only from a single base dir.

If this is true, when you try an open one of the smokeping graphs from
within LibreNMS you will see something like this error at the end of
the rrdcached command:

```bash
ERROR: rrdcached: /var/lib/smokeping/<device name>.rrd: Permission denied
```

You will need to either change the dir in which smokeping saves its
rrd files to be the same as the main librenms dir or you can remove
the -B argument from the rrdcached config to allow it to read from
more than one dir.

To remove the -B switch:

```bash
sudo nano /etc/default/rrdcached
```

then find:

```bash
BASE_OPTIONS=
```

If -B is in the list of arguments delete it.
