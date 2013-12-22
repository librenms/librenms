> NOTE: What follows is a very rough list of commands.  This works on a fresh install of Ubuntu 12.04.

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

Install necessary software.  The packages listed below are an all-inclusive list of packages that were necessary on a clean install of Ubuntu 12.04.

    apt-get install libapache2-mod-php5 php5-cli php5-mysql php5-gd php5-snmp php-pear snmp graphviz php5-mcrypt apache2 fping imagemagick whois mtr-tiny nmap python-mysqldb snmpd mysql-client php-net-ipv4 php-net-ipv6 rrdtool git
    
### Cloning ###

You can clone the repository via HTTPS or SSH.  In either case, you need to ensure the appropriate port (443 for HTTPS, 22 for SSH) is open in the outbound direction for your server.

    cd /opt
    git clone https://github.com/librenms/librenms.git librenms
    cd /opt/librenms
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

To prepare the web interface (and adding devices shortly), you'll need to create and chown a directory as well as create an Apache vhost.

First, create and chown the `rrd` directory and create the `logs` directory

    mkdir rrd logs
    chown www-data:www-data rrd/

> NOTE: If you're not running Ubuntu, you may need to change the owner to whomever the webserver runs as.

Next, add the following to `/etc/apache2/sites-available/librenms.conf`

    <VirtualHost *:80>
      DocumentRoot /opt/librenms/html/
      ServerName  librenms.example.com
      CustomLog /opt/librenms/logs/access_log combined
      ErrorLog /opt/librenms/logs/error_log
      <Directory "/opt/librenms/html/">
        AllowOverride All
        Options FollowSymLinks MultiViews
      </Directory>
    </VirtualHost>

Don't forget to change 'example.com' to your domain, then enable the vhost and restart Apache:

    a2ensite librenms.conf
    a2enmod rewrite
    service apache2 restart

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


