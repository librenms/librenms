
> NOTE: These instructions have been tested on a fresh install of Ubuntu 12.04 and 14.04.

> NOTE: These instructions assume you are the root user.  If you are not, prepend `sudo` to the shell commands (the ones that aren't at `mysql>` prompts) or temporarily become a user with root privileges with `sudo -s` or `sudo -i`.

### On the DB Server ###

This host is where the MySQL database runs.  It could be the same machine as your network management server (this is the most common initial deployment scenario).

    apt-get install mysql-server mysql-client
    mysql -uroot -p

Input the MySQL root password to enter the MySQL command-line interface.

Create the database:

```sql
CREATE DATABASE librenms;
GRANT ALL PRIVILEGES ON librenms.*
  TO 'librenms'@'<ip>'
  IDENTIFIED BY '<password>'
;
FLUSH PRIVILEGES;
exit
```

Replace `<ip>` above with the IP or DNS name of the server running LibreNMS.  If your database is on the same server as LibreNMS, you can use `localhost`.

If you are deploying a separate database server, you need to change the `bind-address`.  If your MySQL database resides on the same server as LibreNMS, you should skip this step.

    vim /etc/mysql/my.cnf

Find the line: `bind-address = 127.0.0.1`

Change `127.0.0.1` to the IP address that your MySQL server should listen on.  Restart MySQL:

    service mysql restart

### On the NMS ###

This host is where the web server and SNMP poller run.  It could be the same machine as your database server.

Install the required software:

    apt-get install libapache2-mod-php5 php5-cli php5-mysql php5-gd php5-snmp php-pear php5-curl snmp graphviz php5-mcrypt php5-json apache2 fping imagemagick whois mtr-tiny nmap python-mysqldb snmpd mysql-client php-net-ipv4 php-net-ipv6 rrdtool git

The packages listed above are an all-inclusive list of packages that were necessary on a clean install of Ubuntu 12.04/14.04.

You need to configure snmpd appropriately if you have not already done so.  An absolute minimal config for snmpd is:

    rocommunity public 127.0.0.1

Adding the above line to `/etc/snmp/snmpd.conf` and running `service snmpd restart` will activate this config.

In `/etc/php5/apache2/php.ini` and `/etc/php5/cli/php.ini`, ensure date.timezone is set to your preferred time zone.  See http://php.net/manual/en/timezones.php for a list of supported timezones.  Valid
examples are: "America/New York", "Australia/Brisbane", "Etc/UTC".

### Adding the librenms-user ###

    useradd librenms -d /opt/librenms -M -r
    usermod -a -G librenms www-data

### Cloning ###

LibreNMS is installed using git.  If you're not familiar with git, check out the [git book][2] or the tips at [git ready][3].  The initial install from github.com is called a `git clone`; subsequent updates are done through `git pull`.

You can clone the repository via HTTPS or SSH.  In either case, you need to ensure that the appropriate port (443 for HTTPS, 22 for SSH) is open in the outbound direction for your server.

    cd /opt
    git clone https://github.com/librenms/librenms.git librenms
    cd /opt/librenms

The recommended method of cloning a git repository is HTTPS.  If you would like to clone via SSH instead, use the command `git clone git@github.com:librenms/librenms.git librenms` instead.

Sometimes the initial clone can take quite a while (nearly 3 minutes on a 10 Mbps fibre connection in Australia is a recent example).  If it's a big problem to you, you can save about 50% of the bandwidth by not pulling down the full git history.  This comes with some limitations (namely that you can't use it as the basis for further git repos), but if you're not planning to develop for LibreNMS it's an acceptable option.  To perform the initial clone without full history, run the following instead:

    cd /opt
    git clone --depth 1 https://github.com/librenms/librenms.git librenms
    cd /opt/librenms


### Web Interface ###

To prepare the web interface (and adding devices shortly), you'll need to create and chown a directory as well as create an Apache vhost.

First, create and chown the `rrd` directory and create the `logs` directory:

    mkdir rrd logs
    chown www-data:www-data logs
    chmod 775 rrd
    chown librenms:librenms rrd

> NOTE: If you're not running Ubuntu or Debian, you will need to change `www-data` to the user and group which run the Apache web server.
> If you're planing on running rrdcached, make sure that the path is also chmod'ed to 775 and chown'ed to librenms:librenms.

Next, add the following to `/etc/apache2/sites-available/librenms.conf`:

```apache
<VirtualHost *:80>
  DocumentRoot /opt/librenms/html/
  ServerName  librenms.example.com
  CustomLog /opt/librenms/logs/access_log combined
  ErrorLog /opt/librenms/logs/error_log
  AllowEncodedSlashes On
  <Directory "/opt/librenms/html/">
    AllowOverride All
    Options FollowSymLinks MultiViews
  </Directory>
</VirtualHost>
```

If you are running Apache 2.2.18 or higher then change AllowEncodedSlashes to NoDecode

If you have Apache 2.3 or newer then please add the following line before `AllowOverride All`:

    Require all granted

On at least Ubuntu 14.04 (and possibly other distributions and versions as well), mcrypt is not enabled on install.  Run the following to enable it:

    php5enmod mcrypt

Change `librenms.example.com` to the appropriate hostname for your domain, then enable the vhost and restart Apache:

    a2ensite librenms.conf
    a2enmod rewrite
    service apache2 restart

If this is the only site you are hosting on this server (it should be :)) then you will need to disable the default site setup in Ubuntu:

    a2dissite 000-default

(To get to your LibreNMS install externally, you'll also need add it to your DNS or hosts file.)

### Manual vs. web installer ###

At this stage you can either launch the web installer by going to http://librenms.example.com/install.php, follow the onscreen instructions then skip to the 'Add localhost' section. Alternatively if you want to continue the setup manually then just keep following these instructions.

    cp config.php.default config.php
    vim config.php

Change the values to the right of the equal sign for lines beginning with `$config[db_]` to match your database information as setup above.

Change the value of `$config['snmp']['community']` from `public` to whatever your read-only SNMP community is.  If you have multiple communities, set it to the most common.

** Be sure you have no characters (including whitespace like: newlines, spaces, tabs, etc) outside of the `<?php?>` blocks. Your graphs will break otherwise. **

### Initialise the database ###

Initiate the follow database with the following command:

    php build-base.php

### Create admin user ###

Create the admin user - priv should be 10

    php adduser.php <name> <pass> 10

Substitute your desired username and password--and leave the angled brackets off.

### Add localhost ###

    php addhost.php localhost public v2c

This assumes you haven't made community changes--if you have, replace `public` with your community.  It also assumes SNMP v2c.  If you're using v3, there are additional steps (NOTE: instructions for SNMPv3 to come).

Discover localhost and poll it for the first time:

    php discovery.php -h all && php poller.php -h all

### Run Daemon ###

#### Running it through cron

We recommend running the daemon through cron to have watchdog capabilities. This way the cron will attempt to restart the daemon in case it exists unexpectedly.

```crontab
*    *    * * *   root    /opt/librenms/librenmsd start >> /dev/null 2>&1
```

#### Running it as init-script

On most systems, you can simply symlink `/etc/init.d/librenmd` to `/opt/librenms/librenmsd`.
The daemon comes with LSB Compliant headers, on a debian system you would issue `insserv librenmsd` or similar to autogenerate the runlevel links.

On a RHEL/Centos system prior to 7, you need to issue `chkconfig --add librenmsd`.

On a distribution using systemd (RHEL/Centos 7 or later) you'll need to create a `librenmsd.service` file yourself and put it in the correct directory.
Here is a skelleton:
```systemd
[Unit]
Description=LibreNMS Daemon
After=syslog.target

[Service]
ExecStart=/opt/librenmsd foreground

[Install]
WantedBy=multi-user.target
```

__In case you do not run librenms in /opt/librenms, you're `REQUIRED` to adjust the `$BASEDIR` varible in the top of the `librenmsd` file.__

More details on the Daemon and it's config are at [Extensions/Daemon](http://docs.librenms.org/Extensions/Daemon/)

### ... or Create cronjob ####

LibreNMS uses Job Snijders' [poller-wrapper.py][1].  By default, the cron job runs `poller-wrapper.py` with 16 threads.  The current recommendation is to use 4 threads per core as a rule of thumb.  If the thread count needs to be changed, you can do so by editing the cron file (`/etc/cron.d/librenms`).  Just add a number after `poller-wrapper.py`, as in the example below:

    /opt/librenms/poller-wrapper.py 12 >> /dev/null 2>&1

Create the cronjob

    cp librenms.nonroot.cron /etc/cron.d/librenms

### Daily Updates ###

LibreNMS performs daily updates by default.  At 00:15 system time every day, a `git pull --no-edit --quiet` is performed.  You can override this default by editing your `config.php` file.  Remove the comment (the `#` mark) on the line:

    #$config['update'] = 0;

so that it looks like this:

    $config['update'] = 0;

### Install complete ###

That's it!  You now should be able to log in to http://librenms.example.com/.  Please note that we have not covered HTTPS setup in this example, so your LibreNMS install is not secure by default.  Please do not expose it to the public Internet unless you have configured HTTPS and taken appropriate web server hardening steps.

It would be great if you would consider opting into the stats system we have, please see [this page](http://docs.librenms.org/General/Callback-Stats-and-Privacy/) on what it is and how to enable it.

[1]: https://github.com/Atrato/observium-poller-wrapper
[2]: http://git-scm.com/book
[3]: http://gitready.com/
