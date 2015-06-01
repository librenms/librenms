#!/bin/bash
#
# Script to install LibreNMS with default configurations, providing to the user an initial contact with the tool.
# Supported OS: Ubuntu 14.04 LTS, Ubuntu 12.04 LTS
#
# Based on http://docs.librenms.org/Installation/Installation-%28Debian-Ubuntu%29/
# Author Joubert RedRat

export DEBIAN_FRONTEND=noninteractive

random_password() {
    MATRIX='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
    LENGTH=10
    while [ ${n:=1} -le $LENGTH ]; do
        PASS="$PASS${MATRIX:$(($RANDOM%${#MATRIX})):1}"
        let n+=1
    done
    echo "$PASS"
}

function check_root {
    if [ "x$(id -u)" != 'x0' ]; then
        echo 'Error: this script can only be executed by root'
        exit 1
    fi
}

function check_os {
    if [ -e '/etc/redhat-release' ]; then
        echo 'Error: sorry, this installer works only on Debian or Ubuntu'
        exit 1
    fi  
}

function check_installed {   
    tmpfile=$(mktemp -p /tmp)
    dpkg --get-selections > $tmpfile
    for pkg in mysql-server apache2 php5; do
        if [ ! -z "$(grep $pkg $tmpfile)" ]; then
            conflicts="$pkg $conflicts"
        fi
    done
    rm -f $tmpfile
    if [ ! -z "$conflicts" ] && [ -z "$force" ]; then
        echo 'Error: This script runs only on a clean installation'
        echo 'Following packages are already installed:'
        echo "$conflicts"
        exit 1
    fi
}

function install_mysql {
    mysqlrootpassword=$(random_password)
    mysqllibrepassword=$(random_password)
    apt-get install -y mysql-server mysql-client mysql-common
    service mysql stop > /dev/null 2>&1

    /usr/sbin/mysqld --skip-grant-tables --skip-networking &

    echo "UPDATE user SET Password=PASSWORD('$mysqlrootpassword') WHERE User='root'; FLUSH PRIVILEGES; exit;" | mysql -u root mysql

    service mysql stop > /dev/null 2>&1
    service mysql start

    echo "CREATE DATABASE librenms;
            GRANT ALL PRIVILEGES ON librenms.*
            TO 'librenms'@'localhost'
            IDENTIFIED BY '$mysqllibrepassword';
            FLUSH PRIVILEGES;" | mysql -u root -p$mysqlrootpassword  
}

function install_dependences {
    apt-get update
    apt-get upgrade -y
    apt-get install -y bsdutils curl fping git graphviz imagemagick mtr-tiny nmap python-mysqldb rrdtool whois
}

function install_webserver {
    apt-get install -y apache2 libapache2-mod-php5 php5 php5-cli php5-curl php5-gd php5-json php5-mcrypt php5-mysql php-pear php5-snmp php-net-ipv4 php-net-ipv6

    if [[ $(apache2 -v | head -n 1) =~ 'Apache/2.4' ]]; then
        echo '<VirtualHost *:80>
                DocumentRoot /opt/librenms/html/
                ServerName localhost.localdomain
                CustomLog /opt/librenms/logs/access_log combined
                ErrorLog /opt/librenms/logs/error_log
                AllowEncodedSlashes On
                <Directory "/opt/librenms/html/">
                    AllowOverride All
                    Options FollowSymLinks MultiViews
                    Require all granted
                </Directory>
            </VirtualHost>' > /etc/apache2/sites-available/librenms.conf
        a2dissite 000-default            
    else        
        echo '<VirtualHost *:80>
                DocumentRoot /opt/librenms/html/
                ServerName localhost.localdomain
                CustomLog /opt/librenms/logs/access_log combined
                ErrorLog /opt/librenms/logs/error_log
                AllowEncodedSlashes On
                <Directory "/opt/librenms/html/">
                    AllowOverride All
                    Options FollowSymLinks MultiViews
                </Directory>
            </VirtualHost>' > /etc/apache2/sites-available/librenms
        a2dissite default            
    fi

    php5enmod mcrypt
    a2enmod rewrite  
    a2ensite librenms
    service apache2 stop > /dev/null 2>&1
    service apache2 start 
}

function install_snmp {
    apt-get install -y snmp snmpd
    rm /etc/snmp/snmpd.conf
    echo 'rocommunity public 127.0.0.1' > /etc/snmp/snmpd.conf
    service snmpd stop > /dev/null 2>&1
    service snmpd start
}

function install_librenms {
    useradd librenms -d /opt/librenms -M -r
    usermod -a -G librenms www-data
    cd /opt
    git clone https://github.com/librenms/librenms.git librenms
    cd /opt/librenms
    mkdir rrd logs
    chown www-data. logs
    chmod 775 rrd
    chown librenms. rrd

    cp /opt/librenms/config.php.default /opt/librenms/config.php
    sed -i 's/USERNAME/librenms/g' /opt/librenms/config.php
    sed -i "s/PASSWORD/$mysqllibrepassword/g" /opt/librenms/config.php

    cp /opt/librenms/librenms.nonroot.cron /etc/cron.d/librenms

    librepassword=$(random_password)

    /usr/bin/php5 build-base.php
    /usr/bin/php5 addhost.php localhost public v2c
    /usr/bin/php5 adduser.php admin $librepassword 10
    /usr/bin/php5 discovery.php -h all
    /usr/bin/php5 poller.php -h all

    service apache2 stop > /dev/null 2>&1
    service apache2 start 
}

function its_done {
    host=$(curl -s http://whatismyip.akamai.com)
    echo
    echo ' ### Done! ###'
    echo
    echo 'LibreNMS was successfully installed, be happy :)'
    echo
    echo "Url: http://$host"
    echo "Username: admin"
    echo "Password: $librepassword"
    echo "Mysql root password: $mysqlrootpassword"
}

function install {
    check_root
    check_os
    check_installed
    install_dependences
    install_mysql
    install_webserver
    install_snmp
    install_librenms
    its_done
}

echo
echo
echo ' ### LibreNMS Faststart ###'
echo
echo 'This script will install librenms in your environment for inicial use and small tests'
echo
echo 'Note: To install this script only works to run on clean OS'
read -p 'Do you want to proceed? [y/n]: ' answer
if [ "$answer" != 'y' ] && [ "$answer" != 'Y'  ]; then
    echo 'Goodbye'
    exit 1
fi

install
