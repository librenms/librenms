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

Note if not using HTTPd (Apache): RHEL requires `httpd` to be installed regardless of of `nginx`'s (or any other web-server's) presence.

    rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
    yum install php php-cli php-gd php-mysql php-snmp php-pear httpd net-snmp graphviz graphviz-php mysql ImageMagick jwhois nmap mtr rrdtool MySQL-python net-snmp-utils vixie-cron php-mcrypt fping git
    pear install Net_IPv4-1.3.4
    pear install Net_IPv6-1.2.2b2

### Using HTTPd (Apache2) ###

Set `httpd` to start on system boot.

    chkconfig --levels 235 httpd on

Next, add the following to `/etc/httpd/conf.d/librenms.conf`

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

If you are running Apache 2.2.18 or higher then change `AllowEncodedSlashes` to `NoDecode`

### Using Nginx and PHP-FPM ###

Install necessary extra software and let it start on system boot.

    yum install nginx php-fpm
    chkconfig --levels 235 nginx on
    chkconfig --levels 235 php-fpm on

Modify permissions and configuration for `php-fpm` to use nginx credentials.

    chown root:nginx /var/lib/php -R
    vi /etc/php-fpm.d/www.conf      # At line #12: Change `listen` to `/var/run/php5-fpm.sock`
                                    # At line #39-41: Change the `user` and `group` to `nginx`

Add configuration for `nginx` at `/etc/nginx/conf.d/librenms` with the following content:

    server {
     listen      80;
     server_name librenms.example.com;
     root        /opt/librenms/html;
     index       index.php;
     access_log  /opt/librenms/logs/access_log;
     error_log   /opt/librenms/logs/error_log;
     location / {
      try_files $uri $uri/ @librenms;
     }
     location ~ \.php {
      include fastcgi.conf;
      fastcgi_split_path_info ^(.+\.php)(/.+)$;
      fastcgi_pass unix:/var/run/php5-fpm.sock;
     }
     location ~ /\.ht {
      deny all;
     }
     location @librenms {
      rewrite ^api/v0(.*)$ /api_v0.php/$1 last;
      rewrite ^(.+)$ /index.php/$1 last;
     }
    }

### Cloning ###

You can clone the repository via HTTPS or SSH.  In either case, you need to ensure the appropriate port (443 for HTTPS, 22 for SSH) is open in the outbound direction for your server.

    cd /opt
    git clone https://github.com/librenms/librenms.git librenms
    cd /opt/librenms

At this stage you can either launch the web installer by going to http://IP/install.php, follow the on-screen instructions then skip to the 'Web Interface' section further down. Alternatively if you want
to continue the setup manually then just keep following these instructions.

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
    # For HTTPd (Apache):
    chown apache:apache rrd/
    # For Nginx:
    chown nginx:nginx rrd/

Start the web-server:

    # For HTTPd (Apache):
    service httpd restart
    # For Nginx:
    service nginx restart

### Add localhost ###

    php addhost.php localhost public v2c

This assumes you haven't made community changes--if you have, replace `public` with your community.  It also assumes SNMP v2c.  If you're using v3, there are additional steps (NOTE: instructions for SNMPv3 to come).

Discover localhost and poll it for the first time:

    php discovery.php -h all && php poller.php -h all

The polling method used by LibreNMS is `poller-wrapper.py`, which was placed in
the public domain by its author.  By default, the LibreNMS cronjob runs `poller-
wrapper.py` with 16 threads.  The current LibreNMS recommendation is to use 4 th
reads per core.  The default if no thread count is `16 threads`.

If the thread count needs to be changed, you can do so by editing `librenms.cron
` before copying (or by editing `/etc/cron.d/librenms` if you've already copied the cron file).  Just add a number after `poller-wrapper.py`, as in the below ex
ample:

    /opt/librenms/poller-wrapper.py 12 >> /dev/null 2>&1

Create the cronjob

    cp librenms.cron /etc/cron.d/librenms

### Daily Updates ###

LibreNMS performs daily updates by default.  At 00:15 system time every day, a `git pull --no-edit --quiet` is performed.  You can override this default by edit
ing your `config.php` file.  Remove the comment (the `#` mark) on the line:

    #$config['update'] = 0;

so that it looks like this:

    $config['update'] = 0;
