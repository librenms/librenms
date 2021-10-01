source: Extensions/Smokeping.md
path: blob/master/doc/

# Smokeping integration

[SmokePing](https://oss.oetiker.ch/smokeping/) is a tool which lets us keep
track of network latency, and visualise this through RRD graphs.

LibreNMS has support for both new and pre-existing SmokePing installations.

For new installations, we can use the `lnms` cli to generate a Smokeping
configuration file.

## Pre-Existing Smokeping Installation

If you have an existing smokeping server, follow the instructions, you only need
to look at [Configure LibreNMS - All Operating Systems](#configure-librenms-all-operating-systems).

## New Installation

All installation steps assume a clean configuration - if you have an existing
smokeping setup, you'll need to adapt these steps somewhat.

### Install and integrate Smokeping Backend - RHEL, CentOS and alike

Smokeping is available via EPEL, which if you're running LibreNMS, you probably
already have. If you want to do something like run Smokeping on a seperate host
and ship data via RRCached though, here's the install command:

```bash
sudo yum install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
sudo yum install smokeping
```

Once installed, you should need a cron script installed to make sure that the
configuration file is updated. You can find an example in `misc/librenms-smokeping-rhel.example`.
Put this into /etc/cron.d/hourly, and mark it executable:

```
sudo cp /opt/librenms/misc/smokeping-rhel.example /etc/cron.hourly/librenms-smokeping
sudo chmod +x /etc/cron.hourly/librenms-smokeping
```

Finally, update the default configuration. Strip *everything* from the
`*** Probes ***` and `*** Targets ***` stanza's, and replace with:

```
*** Probes ***

@include /etc/smokeping/librenms-probes.conf
```

```
*** Targets ***

probe = FPing

menu = Top
title = Network Latency Grapher
remark = Welcome to the SmokePing website of <b>Insert Company Name Here</b>. \
         Here you will learn all about the latency of our network.

@include /etc/smokeping/librenms-targets.conf
```

Note there may be other stanza's (possibly `*** Slaves ***`) between the
`*** Probes ***` and `*** Targets ***` stanza's - leave these intact.

Leave everything else untouched. If you need to add other configuration, make
sure it comes *after* the LibreNMS configuration, and keep in mind that
Smokeping does not allow duplicate modules, and cares about the configuration
file sequence.

Once you're happy, manually kick off the cron once, then enable and start
smokeping:

```bash
sudo /etc/cron.hourly/librenms-smokeping
sudo systemctl enable --now smokeping
```

### Install and integrate Smokeping Backend - Ubuntu, Debian and alike

Smokeping is available via the default repositories.

```bash
sudo apt-get install smokeping
```

Once installed, you should need a cron script installed to make sure that the
configuration file is updated. You can find an example in `misc/librenms-smokeping-debian.example`.
Put this into /etc/cron.d/hourly, and mark it executable:

```
sudo cp /opt/librenms/misc/smokeping-debian.example /etc/cron.hourly/librenms-smokeping
sudo chmod +x /etc/cron.hourly/librenms-smokeping
```

Finally, update the default configuration. Strip *everything* from
`/etc/smokeping/config.d/Probes` and replace with:

```
*** Probes ***

@include /etc/smokeping/config.d/librenms-probes.conf
```

Strip *everything* from `/etc/smokeping/config.d/Targets` and replace with:

```
*** Targets ***

probe = FPing

menu = Top
title = Network Latency Grapher
remark = Welcome to the SmokePing website of <b>Insert Company Name Here</b>. \
         Here you will learn all about the latency of our network.

@include /etc/smokeping/config.d/librenms-targets.conf
```

Leave everything else untouched. If you need to add other configuration, make
sure it comes *after* the LibreNMS configuration, and keep in mind that
Smokeping does not allow duplicate modules, and cares about the configuration
file sequence.

## Configure LibreNMS - All Operating Systems

Edit `/opt/librenms/config.php` and add the following:

```php
$config['smokeping']['dir'] = '/var/lib/smokeping';
$config['smokeping']['pings'] = 20;
$config['smokeping']['probes'] = 2;
$config['smokeping']['integration'] = true;
$config['smokeping']['url'] = 'smokeping/';  // If you have a specific URL or path for smokeping
```
`dir` should match the location that smokeping writes RRD's to
`pings` should match the default smokeping value, default 20
`probes` should be the number of processes to spread pings over, default 2

These settings can also be set in the Web UI.

## Configure Smokeping's Web UI - Optional

This section covers the required configuration for your web server of
choice. This covers the required configuration for either Apache or Nginx.

LibreNMS does not need the Web UI - you can find the graphs in LibreNMS on the
*latency* tab.

### Apache Configuration - Ubuntu, Debian and alike

Edit the `General` configuration file's **Owner** and **contact**, and
**cgiurl hostname** details:

```bash
nano /etc/smokeping/config.d/General
owner    = LibreNMS-Admin
contact  = admin@ACME.xxx
cgiurl   = http://yourlibrenms/cgi-bin/smokeping.cgi
```

Smokeping should automatically install an Apache configuration file in
`/etc/apache2/conf-available/`. Verify this using :

```bash
librenms@librenms:~/scripts$ ls /etc/apache2/conf-available/ | grep smokeping
smokeping.conf
```

If you don't see `smokeping.conf` listed, you'll need to create a symlink for
it:

```bash
ln -s /etc/smokeping/apache2.conf /etc/apache2/conf-available/smokeping.conf
```

After creating the symlink, restart Apache with `sudo systemctl apache2 restart`

You should be able to load the Smokeping web interface at `http://yourhost/cgi-bin/smokeping.cgi`

### Nginx Configuration - Ubuntu, Debian and alike

This section assumes you have configured LibreNMS with Nginx as
specified in [Configure Nginx](../Installation/Installation-Ubuntu-1804-Nginx.md).

Note, you need to install fcgiwrap for CGI wrapper interact with Nginx

```
apt install fcgiwrap
```
Then configure Nginx with the default configuration

```
cp /usr/share/doc/fcgiwrap/examples/nginx.conf /etc/nginx/fcgiwrap.conf
```

Add the following configuration to your `/etc/nginx/conf.d/librenms` config file.

The following will configure Nginx to respond to `http://yourlibrenms/smokeping`:

```
# Browsing to `http://yourlibrenms/smokeping/` should bring up the smokeping web interface

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

After saving the configuration file, verify your Nginx configuration file syntax
is OK with `sudo nginx -t`, then restart Nginx with `sudo systemctl restart nginx`

You should be able to load the Smokeping web interface at `http://yourlibrenms/smokeping`

#### Nginx Password Authentication

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
                auth_basic_user_file /etc/nginx/.htpasswd;
        }
```

## Common Problems

### RRDs::update ERROR: opening ... Permission denied
There is a problem writing to the RRD directory. This is somewhat out of scope
of LibreNMS, but make sure that file permissions and SELinux labels allow the
smokeping user to write to the directory.

If you're using RRDCacheD, make sure that the permissions are correct there too,
and that if you're using -B that the smokeping RRD's are inside the base
directory; update the smokeping rrd directory if required.

It's not recommended to run RRDCachedD without the -B switch.

#### Share RRDCached with LibreNMS

Move the RRD's and give smokeping access rights to the LibreNMS RRD directory:
```bash
sudo systemctl stop smokeping
sudo mv /var/lib/smokeping /opt/librenms/rrd/
sudo usermod -a -G librenms smokeping
```

Update data directory in */etc/smokeping*:

```
datadir = /opt/librenms/rrd/smokeping
dyndir = /opt/librenms/rrd/smokeping/__cgi
```

Finally restart the smokeping service:

```bash
sudo systemctl start smokeping
```

Remember to update *config.php* with the new locations.

### Probe FPing missing missing from the probes section

Take a look at the instructions again - something isn't correct in your
configuration.

### Section or variable already exists

Most likely, content wasn't fully removed from the `*** Probes ***`
`*** Targets***` stanza's as instructed.
If you're trying to integrate LibreNMS, smokeping *and* another source of
configuration, you're probably trying to redefine a module (e.g. '+ FPing' more
than once) or stanza. Otherwise, look again at the instructions.

### Mandatory variable 'probe' not defined
The target block must have a default probe. If you follow the instructions you
will have one. If you're trying to integrate LibreNMS, smokeping *and* another
source of configuration, you need to make sure there are no duplicate or missing
definitions.

### File '/usr/sbin/sendmail' does not exist`

If you got this error at the end of the installation, simply edit or
comment out the sendmail entry in the configuration:

```diff
-sendmail = /usr/sbin/sendmail
+#sendmail = /usr/sbin/sendmail
```
