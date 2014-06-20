> NOTE: What follows is a very rough list of commands. I have taken the INSTALL.md and modified it for Lighttpd on Debian 7

> NOTE: These instructions assume you are the root user.  If you are not, prepend `sudo` to all shell commands (the ones that aren't at `mysql>` prompts) or temporarily become a user with root privileges with `sudo -s`.

## On the DB Server ##

    apt-get install mysql-server mysql-client snmpd
    mysql -uroot -p

Enter the MySQL root password to enter the MySQL command-line interface.

Create database.

    CREATE DATABASE librenms;
    GRANT ALL PRIVILEGES ON librenms.*
      TO 'librenms'@'<ip>'
      IDENTIFIED BY '<password>'
    ;
    FLUSH PRIVILEGES;
    exit

Replace `<ip>` above with the IP of the server running LibreNMS.  If your database is on the same server as LibreNMS, you can just use `localhost` as the IP address.

If you are deploying a separate database server, you need to change the `bind-address`.  If your MySQL database resides on the same server as LibreNMS, you should skip this step.

    vim /etc/mysql/my.cnf

Find the line: `bind-address = 127.0.0.1`

Change `127.0.0.1` to the IP address that your MySQL server should listen on.  Restart MySQL:

    service mysql restart

## On the NMS ##

Install necessary software.  The packages listed below are an all-inclusive list of packages that were necessary on a clean install of Debian 7.

    apt-get install lighttpd php5-cli php5-mysql php5-gd php5-snmp php5-cgi php-pear snmp graphviz mysql-server mysql-client rrdtool sendmail \
fping imagemagick whois mtr-tiny nmap ipmitool php5-mcrypt php5-json python-mysqldb snmpd php-net-ipv4 php-net-ipv6 rrdtool git
    
### Cloning ###

You can clone the repository via HTTPS or SSH.  In either case, you need to ensure the appropriate port (443 for HTTPS, 22 for SSH) is open in the outbound direction for your server.

    cd /opt
    git clone https://github.com/librenms/librenms.git librenms
    cd /opt/librenms

At this stage you can either launch the web installer by going to http://IP/install.php, follow the onscreen instructions then skip to the 'Web Interface' section further down. Alternatively if you want to continue the setup manually then just keep following these instructions.

    cp config.php.default config.php
    vim config.php
    
> NOTE: The recommended method of cloning a git repository is HTTPS.  If you would like to clone via SSH instead, use the command `git clone git@github.com:librenms/librenms.git librenms` instead.

Change the values to the right of the equal sign for lines beginning with `$config[db_]` to match your database information as setup above.

Change the value of `$config['snmp']['community']` from `public` to whatever your read-only SNMP community is.  If you have multiple communities, set it to the most common.

Initiate the follow database with the following command:

    php build-base.php

Create the admin user - priv should be 10

    php adduser.php <name> <pass> 10

Substitute your desired username and password--and leave the angled brackets off.

### Web Interface ###

To prepare the web interface (and adding devices shortly), you'll need to set up Lighttpd.

First, create and chown the `rrd` directory and create the `logs` directory

    mkdir rrd logs
    chown www-data:www-data rrd/

Next, add the following to `/etc/lighttpd/librenms.conf`

     server.document-root = "/opt/librenms/html"
     url.rewrite-once = (
       "^/(.*)\.(png|css|jpg|gif|php)$" => "/$0",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6&$7&$8&$9&$10",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6&$7&$8&$9",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6&$7&$8",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6&$7",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/" => "/?page=$1&$2&$3",
       "^/([a-z|0-9\-]+)/(.+)/" => "/?page=$1&$2",
       "^/([a-z|0-9]+)/$" => "/?page=$1"
     )

Next, add the following to `/etc/lighttpd/lighttpd.conf`

     $HTTP["host"] == "example.com" { include "librenms.conf" }

And enable mod_rewrite by uncommenting
     #       "mod_rewrite",
to
             "mod_rewrite",

Don't forget to change 'example.com' to your domain

Enable fastcgi in Lighttpd by running the following commands,

     lighty-enable-mod fastcgi
     lighty-enable-mod fastcgi-php

And add the following to /etc/php5/cgi/php.ini

     cgi.fix_pathinfo = 1
     
then restart Lighttpd:

    service lightpd restart

### Add localhost ###

    php addhost.php localhost public v2c

This assumes you haven't made community changes--if you have, replace `public` with your community.  It also assumes SNMP v2c.  If you're using v3, there are additional steps (NOTE: instructions for SNMPv3 to come).

Discover localhost and poll it for the first time:

    php discovery.php -h all && php poller.php -h all

The polling method used by LibreNMS is `poller-wrapper.py`, which was placed in the public domain by its author.  By default, the LibreNMS cronjob runs `poller-wrapper.py` with 16 threads.  The current LibreNMS recommendation is to use 4 threads per core.  The default if no thread count is `16 threads`.

If the thread count needs to be changed, you can do so by editing `librenms.cron` before copying (or by editing `/etc/cron.d/librenms` if you've already copied the cron file).  Just add a number after `poller-wrapper.py`, as in the below example:

    /opt/librenms/poller-wrapper.py 12 >> /dev/null 2>&1

Create the cronjob

    ln -s librenms.cron /etc/cron.d/librenms

### Daily Updates ###

LibreNMS performs daily updates by default.  At 00:15 system time every day, a `git pull --no-edit --quiet` is performed.  You can override this default by editing your `config.php` file.  Remove the comment (the `#` mark) on the line:

    #$config['update'] = 0;

so that it looks like this:

    $config['update'] = 0;



