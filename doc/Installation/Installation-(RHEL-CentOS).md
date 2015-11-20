> NOTE: These instructions assume you are the root user.  If you are not, prepend `sudo` to all shell commands (except for `mysql>` prompts commands) or temporarily become a user with root privileges with `sudo -s`.

> NOTE: These instructions assume a clean Centos 7.1 system, the instructions have been tested with a clean Centos 6.4 before and should also work.

### Requirements

These packages are required to run a LibreNMS server. Most common initial deployment scenario is to install the database on the same machine. 

> NOTE : if you are **not** using HTTPd (Apache): RHEL requires `httpd` to be installed regardless of of `nginx`'s (or any other web-server's) presence.

```bash
    yum install epel-release
    yum install php php-cli php-gd php-mysql php-snmp php-pear php-curl httpd net-snmp graphviz graphviz-php mariadb ImageMagick jwhois nmap mtr rrdtool MySQL-python net-snmp-utils cronie php-mcrypt fping git mariadb-server mariadb-client
    pear install Net_IPv4-1.3.4
    pear install Net_IPv6-1.2.2b2
```

> Centos 6 : 
> - MariaDB and MySQL are supported (you only need one)
> - cronie (Centos 7) has replaced vixie-cron (Centos 6), on most distro's a cron system has already been installed and this packages is purely reference

### Database setup

Setup the database to start at boot and log in using the command-line interface tool (mysql):

```bash
    chkconfig mariadb on
    service mariadb start
    mysql_secure_installation
    mysql -u root -p
```
> Centos 6 : if you used MySQL, use `mysqld` instead of `mariadb`

Create a database and allow privileges to user `librenms` (default user) and
replace `<password>` with a strong password of your choice.
```sql
    CREATE DATABASE librenms;
    GRANT ALL PRIVILEGES ON librenms.*
      TO 'librenms'@'localhost'
      IDENTIFIED BY '<password>'
    ;
    FLUSH PRIVILEGES;
```
> NOTE : Whilst we are working on ensuring LibreNMS is compatible with MySQL/MariaDB strict mode, for now, please disable this after MySQL/MariaDB is installed. See `/etc/my.conf.d/server.cnf` or `/etc/my.conf`.

**If database server is different from LibreNMS server**

> NOTE : If your database resides on the same server as LibreNMS, you should skip this step. 

Replace `localhost` above with the IP of the server running LibreNMS. Add the following lines below `[server]` in `/etc/my.cnf.d/server.cnf`, after changing `<ip>` to the IP address that your database server should listen on.

```bash
    bind-address = <ip>
    port = 3306
```
Restart MySQL/MariaDB
```bash
    service mariadb restart
```

Then open port 3306 in your firewall. For example : 

```bash
    iptables -A INPUT -i eth0 -p tcp -m tcp --dport 3306 -j ACCEPT
```

### Installation
Installation of LibreNMS is done by cloning the master git repository via HTTPS or SSH.  In either case, you need to ensure the appropriate port (443 for HTTPS, 22 for SSH) is open in the outbound direction for your server.

> NOTE: The recommended method of cloning a git repository is HTTPS.  If you would like to clone via SSH instead, use the command `git clone git@github.com:librenms/librenms.git librenms` instead.

```bash
    cd /opt
    git clone https://github.com/librenms/librenms.git librenms
    cd /opt/librenms
```

Now copy the config from the default and edit using a editor of your choice. (`vim`, `vi`, `nano`, ...)

```bash
    cp config.php.default config.php
```

Change the values to the right of the equal sign for lines beginning with `$config[db_]` to match your database information as setup above.

Change the value of `$config['snmp']['community']` from `public` to whatever your read-only SNMP community is.  If you have multiple communities, set it to the most common.

Add the following line to the end of `config.php`, after checking where the location is of fping using `where fping`.

```bash
    $config['fping'] = "/usr/sbin/fping";
```

>NOTE : ** Be sure you have no characters (including whitespace like: newlines, spaces, tabs, etc) outside of the `<?php ?>` blocks. Your graphs will break otherwise. **

Now we need to create a new user and give him write permissions in a few directories. Create and chown the `rrd` directory and create the `logs` directory.
```bash
    useradd librenms -d /opt/librenms -M -r
    mkdir /opt/librenms/rrd /opt/librenms/logs
    chown -R librenms:librenms /opt/librenms
    chmod 775 /opt/librenms/rrd
```

### Using Apache/Httpd
Start Apache during boot : 
```bash
    chkconfig --levels 235 httpd on
```
Add the librenms-user to apache group: 
```bash
    usermod -a -G librenms apache
```
Allow apache to write logs in our directory
```bash
    chown apache:apache /opt/librenms/logs
```
Next, add the following to the new file `/etc/httpd/conf.d/librenms.conf` after changing the value of `ServerName`

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

> NOTES : If you are running Apache 2.2.18 or higher then : 
> - Change `AllowEncodedSlashes` to `NoDecode`
> - Append `Require all granted` underneath `Options FollowSymLinks MultiViews`.
> - If the file `/etc/httpd/conf.d/welcome.conf` exists, you might want to comment that as well unless you're familiar with [Name-based Virtual Hosts](https://httpd.apache.org/docs/2.2/vhosts/name-based.html)
> - If you're planing on running rrdcached, make sure that the path is also chmod'ed to 775 and chown'ed to librenms:librenms.

After changing these, restart the httpd deamon :

```bash
    service httpd restart
```

### Using Nginx
Add the librenms-user for nginx :
```bash
    usermod -a -G librenms nginx
```

Install necessary extra software and let it start on system boot.

```bash
    yum install nginx php-fpm
    chkconfig nginx on
    chkconfig php-fpm on
```
Allow nginx to write logs in `/opt/librenms/logs`

```bash 
    chown nginx:nginx /opt/librenms/logs
```

Modify permissions and configuration for `php-fpm` to use nginx credentials.

```bash
    mkdir /var/lib/php/session
    chown root:nginx /var/lib/php -R
    vim /etc/php-fpm.d/www.conf      # At line #12: Change `listen` to `/var/run/php5-fpm.sock`
                                    # At line #39-41: Change the `user` and `group` to `nginx`
```
Add configuration for `nginx` at `/etc/nginx/conf.d/librenms.conf` with the following content:

```nginx
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
  rewrite api/v0(.*)$ /api_v0.php/$1 last;
  rewrite ^(.+)$ /index.php/$1 last;
 }
}
```
> If you're planing on running rrdcached, make sure that the path is also chmod'ed to 775 and chown'ed to librenms:librenms.

After these changes restart nginx
```bash
    service nginx restart
```

### Initialise the database

Initiate the database with the following command from within `/opt/librenms`:
```bash
    php build-base.php
```

### Create admin user ###

Create the admin user - priv should be 10 from within `/opt/librenms`:
```bash
    php adduser.php <name> <pass> 10 <email>
```
Substitute your desired `<name>`, `<password>` and `<email>` address--and leave the angled brackets off.

### SELinux
If you're using SELinux you need to allow web server user to write into logs directory. semanage tool is a part of policycoreutils-python, so if don't have it, you can install it
> NOTE: Running LibreNMS with SELinux is still experimental and we cannot guarantee that everything will be working fine for now.

```bash
    yum install policycoreutils-python
```

```bash
    semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/logs(/.*)?'
    semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/logs(/.*)?'
    restorecon -RFvv /opt/librenms/logs/
```

### Set up snmpd

Since we installed snmpd we can now start polling from machines. If you have not yet done, configure snmpd. This is done in `/etc/snmp/snmpd.conf` by at least adding :
```bash
    rocommunity public 127.0.0.1
```
Change `127.0.0.1` to the IP of the libreNMS server for machines other than localhost.

Now we can start the service and configure it to start at boot.

```bash
    chkconfig snmpd on
    service snmpd start
```

### Manual add localhost
Now we can add our first server to LibreNMS using :
```bash
    php addhost.php localhost public v2c
```
This assumes you haven't made community changes--if you have, replace `public` with your community.  It also assumes SNMP v2c.  If you're using v3, there are additional steps (NOTE: instructions for SNMPv3 to come).

Discover localhost:
```bash
    php discovery.php -h all
```
### Create cronjob ###
LibreNMS uses Job Snijders' [poller-wrapper.py][1].  By default, the cron job runs `poller-wrapper.py` with 16 threads.  The current recommendation is to use 4 threads per core as a rule of thumb.  If the thread count needs to be changed, you can do so by editing the cron file (`/etc/cron.d/librenms`).  Just add a number after `poller-wrapper.py`, as in the example below:

    /opt/librenms/poller-wrapper.py 12 >> /dev/null 2>&1

Create the cronjob

    cp librenms.nonroot.cron /etc/cron.d/librenms

### Daily Updates ###

LibreNMS performs daily updates by default.  At 00:15 system time every day, a `git pull --no-edit --quiet` is performed.  You can override this default by edit
ing your `config.php` file.  Remove the comment (the `#` mark) on the line:

    #$config['update'] = 0;

so that it looks like this:

    $config['update'] = 0;

### Install complete ###

Please allow for 2-3 runs of the poller-wrapper for data to start appearing in the WebUI.
If you don't see data after this, please refer to the [FAQ](http://docs.librenms.org/Support/FAQ/) for assistance.

That's it!  You now should be able to log in to http://librenms.example.com/.  Please note that we have not covered HTTPS setup in this example, so your LibreNMS install is not secure by default.  Please do not expose it to the public Internet unless you have configured HTTPS and taken appropriate web server hardening steps.

It would be great if you would consider opting into the stats system we have, please see [this page](http://docs.librenms.org/General/Callback-Stats-and-Privacy/) on what it is and how to enable it.

[1]: https://github.com/Atrato/observium-poller-wrapper