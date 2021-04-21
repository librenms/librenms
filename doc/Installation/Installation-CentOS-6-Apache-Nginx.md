source: Installation/Installation-CentOS-6-Apache-Nginx.md
path: blob/master/doc/

> NOTE: These instructions assume you are the **root** user.  If you
> are not, prepend `sudo` to the shell commands (the ones that aren't
> at `mysql>` prompts) or temporarily become a user with root
> privileges with `sudo -s` or `sudo -i`.

**Please note the minimum supported PHP version is @= php.version_min =@**

# On the DB Server

This host is where the MySQL database runs.  It could be the same
machine as your network management server (this is the most common
initial deployment scenario).

> ** Whilst we are working on ensuring LibreNMS is compatible with
> MySQL strict mode, for now, please disable this after mysql is
> installed.

You are free to choose between using MySQL or MariaDB:

## MySQL

```bash
yum install net-snmp mysql-server mysql-client
chkconfig mysqld on
service mysqld start
```

## MariaDB

```bash
yum install net-snmp mariadb-server mariadb-client
chkconfig mariadb on
service mariadb start
```

## General

Now continue with the installation:

```bash
chkconfig snmpd on
service snmpd start
mysql_secure_installation
mysql -uroot -p
```

Enter the MySQL/MariaDB root password to enter the command-line interface.

Create database.

```sql
CREATE DATABASE librenms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
FLUSH PRIVILEGES;
exit
```

Replace `<ip>` above with the IP of the server running LibreNMS.  If
your database is on the same server as LibreNMS, use `localhost` as
the IP address.

Edit the mysql/mariadb configuration file:

```bash
vim /etc/my.cnf
```

Add the following line:

```
innodb_file_per_table=1
```

If you are deploying a separate database server, you also need to
specify the `bind-address`.  If your MySQL database resides on the
same server as LibreNMS, you should skip this step.

```
bind-address = <ip>
```

If you see a line that starts `sql-mode` then change this to
`sql-mode=""`.

Save, and restart MySQL/MariaDB to apply your changes:

```
service mysqld restart
```

or

```
service mariadb restart
```

# On the NMS

Install necessary software.  The packages listed below are an
all-inclusive list of packages that were necessary on a clean install
of CentOS 6.x.  It also requires the EPEL repository. Net_IPv6 is
required even if your network environment does not support IPv6.

Note if not using HTTPd (Apache): RHEL requires `httpd` to be
installed regardless of of `nginx`'s (or any other web-server's)
presence.

```bash
yum install epel-release
yum install php php-cli php-gd php-mysql php-snmp php-pear php-curl httpd net-snmp graphviz graphviz-php mysql ImageMagick jwhois nmap mtr rrdtool MySQL-python net-snmp-utils vixie-cron php-mcrypt fping git
pear install Net_IPv4-1.3.4
pear install Net_IPv6-1.2.2b2
```

# Configure SNMPD on localhost

Edit `/etc/snmp/snmpd.conf` to enable self-polling.  An absolute
minimal config for snmpd is:

```
rocommunity public 127.0.0.1
```

You may either edit the default configuration file to your liking, or
backup the default config file and replace it entirely with your
own. To apply your changes, run `service snmpd restart` for Centos
6.x. If you have deployed a separate database server, you will likely
want to configure snmpd on that host as well.

# Adding the librenms-user for Apache

```bash
    useradd librenms -d /opt/librenms -M -r
    usermod -a -G librenms apache
```

# Adding the librenms-user for Nginx

```bash
    useradd librenms -d /opt/librenms -M -r
    usermod -a -G librenms nginx
```

# Using HTTPd (Apache2)

Set `httpd` to start on system boot.

```bash
chkconfig --levels 235 httpd on
```

In `/etc/php.ini`, ensure `date.timezone` is set to your preferred
time zone.  See <https://php.net/manual/en/timezones.php> for a list of
supported timezones.  Valid examples are: "America/New York",
"Australia/Brisbane", "Etc/UTC". Please also ensure that
`allow_url_fopen` is enabled. Other functions needed for LibreNMS
include
`exec,passthru,shell_exec,escapeshellarg,escapeshellcmd,proc_close,proc_open,popen`.

Next, add the following to `/etc/httpd/conf.d/librenms.conf`

If you are running Apache below version 2.2.18:

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

If you are running Apache 2.2.18 or higher:

```apache
<VirtualHost *:80>
  DocumentRoot /opt/librenms/html/
  ServerName  librenms.example.com
  CustomLog /opt/librenms/logs/access_log combined
  ErrorLog /opt/librenms/logs/error_log
  AllowEncodedSlashes NoDecode
  <Directory "/opt/librenms/html/">
    AllowOverride All
    Options FollowSymLinks MultiViews
    Require all granted
  </Directory>
</VirtualHost>
```

If the file `/etc/httpd/conf.d/welcome.conf` exists, you will want to
remove that as well unless you're familiar with [Name-based Virtual
Hosts](https://httpd.apache.org/docs/2.2/vhosts/name-based.html).

```bash
rn /etc/httpd/conf.d/welcome.conf /etc/httpd/conf.d/welcome.conf.bak
```

# Using Nginx and PHP-FPM

If you choose to run Nginx, you will need to install necessary extra
software and let it start on system boot.

```bash
    yum install nginx php-fpm
    chkconfig nginx on
    chkconfig php-fpm on
```

Modify permissions and configuration for `php-fpm` to use nginx credentials.

```
mkdir /var/lib/php/session
chown root:nginx /var/lib/php -R
vim /etc/php-fpm.d/www.conf      # At line #12: Change `listen` to `/var/run/php5-fpm.sock`
                                 # At line #39-41: Change the `user` and `group` to `nginx`
```

Add configuration for `nginx` at `/etc/nginx/conf.d/librenms.conf`
with the following content:

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
  fastcgi_param PATH_INFO $fastcgi_path_info;
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

# Cloning

You can clone the repository via HTTPS or SSH.  In either case, you
need to ensure the appropriate port (443 for HTTPS, 22 for SSH) is
open in the outbound direction for your server.

```bash
cd /opt
git clone https://github.com/librenms/librenms.git librenms
cd /opt/librenms
```

NOTE: The recommended method of cloning a git repository is HTTPS.  If
you would like to clone via SSH instead, use the command `git clone
git@github.com:librenms/librenms.git librenms` instead.

# Prepare the Web Interface

To prepare the web interface, you'll need to create and chown a
directory as well as create an Apache vhost.

First, create and chown the `rrd` directory and create the `logs` directory

```bash
mkdir rrd logs
chown -R librenms:librenms /opt/librenms
chmod 775 rrd
```

If you're planning on running rrdcached, make sure that the path is
also chmod'ed to 775 and chown'ed to librenms:librenms.

**SELinux**
> if you're using SELinux you need to allow web server user to write into logs directory.
> semanage tool is a part of policycoreutils-python, so if don't have
it, you can install it  **Please note that running LibreNMS with
SELinux is still experimental and we cannot guarantee that everything
will be working fine for now.**

```bash
    yum install policycoreutils-python
```

```bash
    semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/logs(/.*)?'
    semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/logs(/.*)?'
    restorecon -RFvv /opt/librenms/logs/
```

Set selinux to allow httpd to sendmail

```bash
    setsebool -P httpd_can_sendmail=1
```

# Start the web-server

```
# For HTTPd (Apache):
service httpd restart

# For Nginx:
service nginx restart
```

# Web Installer

At this stage you can launch the web installer by going to
`http://IP/install.php` and follow the on-screen
instructions. Alternatively if you want to continue the setup manually
then perform the manual install steps. If you cannot reach the
installer, stop here and solve that problem before
proceeding. Afterwards, Add the following line to the end of
`config.php`:

```
$config['fping'] = "/usr/sbin/fping";
```

# Manual Install

You may skip this section if the web installer succeeded.

```bash
cp config.php.default config.php
vi config.php
```

Change the values to the right of the equal sign for lines beginning
with `$config[db_]` to match your database information as previously
configured.

Change the value of `$config['snmp']['community']` from `public` to
whatever your default read-only SNMP community is.  If you have
multiple existing communities in your environment, set it to the most
common.

Add the following line to the end of `config.php`:

```
$config['fping'] = "/usr/sbin/fping";
```

Important: Be sure you have no characters (including whitespace like:
newlines, spaces, tabs, etc) outside of the `<?php?>` blocks. Your
graphs will break otherwise and there will be no error messages to
indicate otherwise!

# Initialize the database

Initiate the follow database with the following command:

```
php includes/sql-schema/update.php
```

# Create admin user

Create the admin user - priv should be 10

```
php adduser.php <name> <pass> 10 <email>
```

Substitute your desired username, password and email address--and
leave the angled brackets off.

# Validate your install

After performing the manual install or web install, be sure to run
validate.php as root in the librenms directory:

```
php validate.php
```

This will check your install to verify it is set up correctly.

# Add localhost

```
php addhost.php localhost public v2c
```

Replace `public` to match what you set in `/etc/snmp/snmpd.conf`.
This command also assumes you are using SNMP v2c.  If you're using v3,
there are additional steps (NOTE: instructions for SNMPv3 to come).

Discover localhost:

```
php discovery.php -h all
```

# Create cronjob

The polling method used by LibreNMS is `poller-wrapper.py`, which was
placed in the public domain by its author.  By default, the LibreNMS
cronjob runs `poller-wrapper.py` with 16 threads.  The current
LibreNMS recommendation is to use 4 threads per core.  The default if
no thread count is `16 threads`.

If the thread count needs to be changed, you can do so by editing the cron file (`/etc/cron.d/librenms`).
 Just add a number after `poller-wrapper.py`, as in the below example:

```
/opt/librenms/poller-wrapper.py 12 >> /dev/null 2>&1
```

Create the cronjob

```
cp librenms.nonroot.cron /etc/cron.d/librenms
```

> NOTE: Keep in mind  that cron, by default, only uses a very limited
> set of environment variables. You may need to configure proxy
> variables for the cron invocation. Alternatively adding the proxy
> settings in config.php is possible too. The config.php file will be
> created in the upcoming steps. Review the following URL after you
> finished librenms install steps:
> <@= config.site_url =@/Support/Configuration/#proxy-support>

# Copy logrotate config

LibreNMS keeps logs in `/opt/librenms/logs`. Over time these can
become large and be rotated out.  To rotate out the old logs you can
use the provided logrotate config file:

```
cp misc/librenms.logrotate /etc/logrotate.d/librenms
```

# Daily Updates

LibreNMS performs daily updates by default.  At 00:15 system time
every day, a `git pull --no-edit --quiet` is performed.  You can
override this default by editing your `config.php` file.  Remove the
comment (the `#` mark) on the line:

```
#$config['update'] = 0;
```

so that it looks like this:

```
$config['update'] = 0;
```

# Install complete

Please allow for 2-3 runs of the poller-wrapper for data to start
appearing in the WebUI. If you don't see data after this, please refer
to the [FAQ](../Support/FAQ.md) for assistance.

That's it!  You now should be able to log in to
<http://librenms.example.com/>. Please note that we have not covered
HTTPS setup in this example, so your LibreNMS install is not secure by
default.  Please do not expose it to the public Internet unless you
have configured HTTPS and taken appropriate web server hardening
steps.

It would be great if you would consider opting into the stats system
we have, please see [this page](../General/Callback-Stats-and-Privacy.md) on
what it is and how to enable it.

