NOTE: What follows is a very rough list of commands.  This works on a fresh install of CentOS 6.4.

NOTE: These instructions assume you are the root user.  If you are not, prepend `sudo` to all shell commands (the ones that aren't at `mysql>` prompts) or temporarily become a user with root privileges with `sudo -s`.

## On the DB Server ##

    yum install net-snmp mysql-server
    service snmpd start
    service mysqld start
    chkconfig --levels 235 mysqld on
    chkconfig --levels 235 snmpd on
    mysql_secure_installation
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

    vim /etc/my.cnf

Add the following line:
    
    bind-address = <ip>

Change `<ip>` to the IP address that your MySQL server should listen on.  Restart MySQL:

    service mysqld restart

## On the NMS ##

Install necessary software.  The packages listed below are an all-inclusive list of packages that were necessary on a clean install of CentOS 6.4.  It also requires the EPEL repository.

    rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
    yum install php php-cli php-gd php-mysql php-snmp php-pear httpd net-snmp graphviz graphviz-php mysql ImageMagick  jwhois nmap mtr rrdtool MySQL-python net-snmp-utils vixie-cron php-mcrypt fping git
    pear install Net_IPv4-1.3.4
    pear install Net_IPv6-1.2.2b2
    chkconfig --levels 235 httpd on
    service httpd start
    
### Cloning ###

You can clone the repository via HTTPS or SSH.  In either case, you need to ensure the appropriate port (443 for HTTPS, 22 for SSH) is open in the outbound direction for your server.

    cd /opt
    git clone https://github.com/librenms/librenms.git librenms
    cd /opt/librenms
    cp config.php.default config.php
    vim config.php
    
NOTE: The recommended method of cloning a git repository is HTTPS.  If you would like to clone via SSH instead, use the command `git clone git@github.com:librenms/librenms.git librenms` instead.

Change the values to the right of the equal sign for lines beginning with `$config[db_]` to match your database information as setup above.

Change the value of `$config['snmp']['community']` from `public` to whatever your read-only SNMP community is.  If you have multiple communities, set it to the most common.

Add the following line to the end of `config.php`:

    $config['fping'] = "/usr/sbin/fping";

Initiate the follow database with the following command:

    php build-base.php

Create the admin user - priv should be 10

    php adduser.php <name> <pass> 10

Substitute your desired username and password--and leave the angled brackets off.

### Web Interface ###

To prepare the web interface (and adding devices shortly), you'll need to create and chown a directory as well as create an Apache vhost.

First, create and chown the `rrd` directory and create the `logs` directory

    mkdir rrd logs
    chown apache:apache rrd/

Note that if you're not running Ubuntu, you may need to change the owner to whomever the webserver runs as.

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

Don't forget to restart Apache to make this active:

    service httpd restart

### Add localhost ###

    php addhost.php localhost public v2c

This assumes you haven't made community changes--if you have, replace `public` with your community.  It also assumes SNMP v2c.  If you're using v3, there are additional steps (NOTE: instructions for SNMPv3 to come).

Discover localhost and poll it for the first time:

    php discovery.php -h all && php poller.php -h all

Create the cronjob

    cp librenms.cron /etc/cron.d/librenms

