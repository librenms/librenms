#!/bin/bash
#
# Script to install LibreNMS with default configurations, providing to the user an initial contact with the tool.
# Supported OS: 
#
# Based on http://docs.librenms.org/Installation/Installation-%28RHEL-CentOS%29/
# Author Joubert RedRat

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
	if [ ! -e '/etc/redhat-release' ]; then
	    echo 'Error: sorry, this installer works only on RHEL and CentOS'
	    exit 1
	fi
}

function check_installed {   
    tmpfile=$(mktemp -p /tmp)
    rpm -qa > $tmpfile
    for pkg in mysql-server httpd php5; do
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
    yum install -y mysql mysql-server
    /etc/init.d/mysqld stop > /dev/null 2>&1

    /usr/bin/mysqld_safe --skip-grant-tables --skip-networking &

    echo "UPDATE user SET Password=PASSWORD('$mysqlrootpassword') WHERE User='root'; FLUSH PRIVILEGES;" | mysql -u root mysql

    /etc/init.d/mysqld stop > /dev/null 2>&1
    /etc/init.d/mysqld start

    echo "CREATE DATABASE librenms;
            GRANT ALL PRIVILEGES ON librenms.*
            TO 'librenms'@'localhost'
            IDENTIFIED BY '$mysqllibrepassword';
            FLUSH PRIVILEGES;" | mysql -u root -p$mysqlrootpassword  
}

function install_dependences {
	yum update -y
	rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
	yum install -y curl fping git graphviz ImageMagick jwhois mtr MySQL-python nmap rrdtool vixie-cron
}

function install_webserver {
    yum install -y httpd php php-cli php-curl php-gd php-mcrypt php-mysql php-pear php-snmp graphviz-php
    pear install Net_IPv4-1.3.4
    pear install Net_IPv6-1.2.2b2

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
        </VirtualHost>' > /etc/httpd/conf.d/librenms.conf
          
    /etc/init.d/httpd stop > /dev/null 2>&1
    /etc/init.d/httpd start 
}

function install_snmp {
   	yum install -y net-snmp net-snmp-utils
    rm /etc/snmp/snmpd.conf
    echo 'rocommunity public 127.0.0.1' > /etc/snmp/snmpd.conf
    /etc/init.d/snmpd stop > /dev/null 2>&1
    /etc/init.d/snmpd start
}

function install_librenms {
    useradd librenms -d /opt/librenms -M -r
    usermod -a -G librenms apache
    cd /opt
    git clone https://github.com/librenms/librenms.git librenms
    cd /opt/librenms
    mkdir rrd logs
    chown apache. logs
    chmod 775 rrd
    chown librenms. rrd

    cp /opt/librenms/config.php.default /opt/librenms/config.php
    sed -i 's/USERNAME/librenms/g' /opt/librenms/config.php
    sed -i "s/PASSWORD/$mysqllibrepassword/g" /opt/librenms/config.php
    echo '$config["fping"] = "/usr/sbin/fping";' >> /opt/librenms/config.php

    cp /opt/librenms/librenms.nonroot.cron /etc/cron.d/librenms

    librepassword=$(random_password)

    /usr/bin/php build-base.php
    /usr/bin/php addhost.php localhost public v2c
    /usr/bin/php adduser.php admin $librepassword 10
    /usr/bin/php discovery.php -h all
    /usr/bin/php poller.php -h all

    /etc/init.d/httpd stop > /dev/null 2>&1
    /etc/init.d/httpd start 
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