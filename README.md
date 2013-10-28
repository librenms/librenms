License
-------

Copyright (C) 2006-2012 Adam Armstrong <adama@memetic.org>
Copyright (C) 2013 LibreNMS Group <librenms-project@googlegroups.com>

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

Please see LICENSE.txt for usage requirements and restrictions.


Introduction
------------

LibreNMS is an autodiscovering PHP/MySQL/SNMP based network monitoring
which includes support for a wide range of network hardware and operating
systems including Cisco, Linux, FreeBSD, Juniper, Brocade, Foundry, HP and
many more.

LibreNMS is a community-based fork of the last GPL-licensed version of
Observium <http://observium.org/>.  LibreNMS intends to be a viable network
monitoring project and community that:
- encourages contribution,
- focuses on the needs of its users, and
- offers a welcoming, friendly environment for everyone.

The Debian Social Contract <http://www.debian.org/social_contract> will be
the basis of our priority system, and mutual respect the basis of our
behaviour towards others.


Installation
------------

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
    git clone https://github.com/libertysys/librenms.git librenms
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

### Add localhost ###

    php addhost.php localhost public v2c

This assumes you haven't made community changes--if you have, replace `public` with your community.  It also assumes SNMP v2c.  If you're using v3, there are additional steps (NOTE: instructions for SNMPv3 to come).

Discover localhost

    php discovery.php -h all

First poller

    php poller.php -h all

Create the cronjob

    cp librenms.cron /etc/cron.d/librenms

Contributing
------------

Clone the repo and file bug reports and pull requests here.
Join the [librenms-project][1] mailing list to post questions and suggestions.

[1]: https://groups.google.com/forum/#!forum/librenms-project "LibreNMS"
