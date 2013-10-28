NOTE: What follows is a very rough list of commands.  This works on a fresh install of Ubuntu 12.04.
NOTE: These instructions assume you are using a separate server for your database.  You will need to adjust the instructions if you are running the database on the same server.

## On the DB Server ##

    aptitude install mysql-server mysql-client snmpd
    mysql -uroot -p

enter root mysql password
Create database

    CREATE DATABASE librenms;
    GRANT ALL PRIVILEGES ON librenms.*
      TO 'librenms'@'<ip>'
      IDENTIFIED BY '<password>'
    ;
    FLUSH PRIVILEGES;
    exit

allow MySQL to listen on local LAN

    vim /etc/mysql/my.cnf

edit line 47 (should be `bind-address = 127.0.0.1`)
and change it to your IP address
now restart MySQL

    service mysql restart


## On the NMS ##

    aptitude install libapache2-mod-php5 php5-cli php5-mysql php5-gd php5-snmp php-pear snmp graphviz php5-mcrypt apache2 fping imagemagick whois mtr-tiny nmap python-mysqldb snmpd mysql-client php-net-ipv4 php-net-ipv6 rrdtool
    cd /opt
    git clone https://github.com/librenms/librenms.git librenms
    cd /opt/librenms
    cp config.php.default config.php
    vim config.php

change lines 6-9 to match your db config
change lines 17 and 20 to 'librenms'
change line 31 to match your most common read-only SNMP community string

copy sql commands to db server

    scp -r build.sql <ip>:

Subsitute your database server's IP address.  If it's local host, the above step is unnecessary.

## On DB Server ##

    mysql -ulibrenms -p < build.sql

This assumes you used the username `librenms`.  If you used something different, adjust here.

## On the NMS ##

Create admin user - priv should be 10

    php adduser.php <name> <pass> 10

Substitute your desired username and password--and leave the angled brackets off.

### Web Interface ###

To prepare the web interface (and adding devices shortly), you'll need to create and chown a directory as well as create an Apache vhost.

First, create and chown the `rrd` directory and create the `logs` directory

    mkdir rrd logs
    chown www-data:www-data rrd/

Note that if you're not running Ubuntu, you may need to change the owner to whomever the webserver runs as.

Next, add the following to `/etc/apache2/available-sites/librenms.conf`

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

Don't forget to change 'example.com' to your domain
Enable the vhost and restart Apache

    a2ensite librenms.conf
    a2enmod rewrite
    service apache2 restart

### Add localhost ###

    php addhost.php localhost public v2c

This assumes you haven't made community changes--if you have, replace `public` with your community.  It also assumes SNMP v2c.  If you're using v3, there are additional steps (NOTE: instructions for SNMPv3 to come).

Discover localhost

    php discovery.php -h all

First poller

    php poller.php -h all

Create the cronjob

    cp librenms.cron /etc/cron.d/librenms

