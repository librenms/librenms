source: Installation/Installation-CentOS-7-Apache.md
path: blob/master/doc/

> NOTE: These instructions assume you are the **root** user.  If you
> are not, prepend `sudo` to the shell commands (the ones that aren't
> at `mysql>` prompts) or temporarily become a user with root
> privileges with `sudo -s` or `sudo -i`.

**Please note the minimum supported PHP version is @= php.version_min =@**


## Install Common Required Packages ##

```
yum install epel-release
```

```
yum install git cronie fping jwhois ImageMagick mtr MySQL-python net-snmp net-snmp-utils nmap python-memcached rrdtool policycoreutils-python httpd mariadb mariadb-server unzip python3 python3-pip
```

### Install PHP

CentOS 7 comes with php 5.4 which is not compatible with LibreNMS.
There are multiple ways to install php 7.x on CentOS 7, like Webtatic, Remi or SCL, the last two are maintained by Remi Collet of RedHat.

#### Running with Remi PHP

```
yum localinstall http://rpms.remirepo.net/enterprise/remi-release-7.rpm
```
Install the yum-config-manager to change to Remi PHP 7.3 Repo.
```
yum install yum-utils
yum-config-manager --enable remi-php73
```
Install the required packages

```
yum install mod_php php-cli php-common php-curl php-gd php-mbstring php-process php-snmp php-xml php-zip php-memcached php-mysqlnd
```

#### Running with CentOS SCL php

```
yum install centos-release-scl
```

```
yum install rh-php72 rh-php72-php-cli rh-php72-php-common rh-php72-php-curl rh-php72-php-gd rh-php72-php-mbstring rh-php72-php-process rh-php72-php-snmp rh-php72-php-xml rh-php72-php-zip rh-php72-php-memcached rh-php72-php-mysqlnd
```

```
ln -s /opt/rh/rh-php72/root/usr/bin/php /usr/bin/php
ln -s /opt/rh/httpd24/root/etc/httpd/conf.d/rh-php72-php.conf /etc/httpd/conf.d/
ln -s /opt/rh/httpd24/root/etc/httpd/conf.modules.d/15-rh-php72-php.conf /etc/httpd/conf.modules.d/
ln -s /opt/rh/httpd24/root/etc/httpd/modules/librh-php72-php7.so /etc/httpd/modules/
```

# Add librenms user

```
useradd librenms -d /opt/librenms -M -r
usermod -a -G librenms apache
```

# Download LibreNMS

```
cd /opt
git clone https://github.com/librenms/librenms.git
```

# Set permissions

```
chown -R librenms:librenms /opt/librenms
chmod 770 /opt/librenms
setfacl -d -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/ /opt/librenms/cache
setfacl -R -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/ /opt/librenms/cache
```

# Install PHP dependencies

```
su - librenms
./scripts/composer_wrapper.php install --no-dev
exit
```

# DB Server

## Configure MySQL

```
systemctl enable --now mariadb
mysql -u root
```

> NOTE: Please change the 'password' below to something secure.

```sql
CREATE DATABASE librenms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
FLUSH PRIVILEGES;
exit
```

```
vi /etc/my.cnf
```

Within the `[mysqld]` section please add:

```bash
innodb_file_per_table=1
lower_case_table_names=0
```

```
systemctl restart mariadb
```

# Web Server

## Configure PHP

Ensure date.timezone is set in php.ini to your preferred time zone.
See <https://php.net/manual/en/timezones.php> for a list of supported
timezones.  Valid examples are: "America/New_York",
"Australia/Brisbane", "Etc/UTC".

When PHP is configured with open_basedir, be sure to allow the following paths for LibreNMS to work:

```
php_admin_value[open_basedir] = "/opt/librenms:/usr/lib64/nagios/plugins:/dev/urandom:/usr/sbin/fping:/usr/sbin/fping6:/usr/bin/snmpgetnext:/usr/bin/rrdtool:/usr/bin/snmpwalk:/usr/bin/snmpget:/usr/bin/snmpbulkwalk:/usr/bin/snmptranslate:/usr/bin/traceroute:/usr/bin/whois:/bin/ping:/usr/sbin/mtr:/usr/bin/nmap:/usr/sbin/ipmitool:/usr/bin/virsh:/usr/bin/nfdump"
```

```
vi  /etc/php.ini
```

or

```
vi /etc/opt/rh/rh-php72/php.ini
```



## Configure Apache

Create the librenms.conf:

```
vi /etc/httpd/conf.d/librenms.conf
```

Add the following config, edit `ServerName` as required:

```apache
<VirtualHost *:80>
  DocumentRoot /opt/librenms/html/
  ServerName  librenms.example.com

  AllowEncodedSlashes NoDecode
  <Directory "/opt/librenms/html/">
    Require all granted
    AllowOverride All
    Options FollowSymLinks MultiViews
  </Directory>
</VirtualHost>
```

> NOTE: If this is the only site you are hosting on this server (it
> should be :)) then you will need to disable the default site. `rm -f /etc/httpd/conf.d/welcome.conf`

```
systemctl enable --now httpd
```

# SELinux

Install the policy tool for SELinux:

```
yum install policycoreutils-python
```

## Configure the contexts needed by LibreNMS

```
semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/logs(/.*)?'
semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/logs(/.*)?'
restorecon -RFvv /opt/librenms/logs/
semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/rrd(/.*)?'
semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/rrd(/.*)?'
restorecon -RFvv /opt/librenms/rrd/
semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/storage(/.*)?'
semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/storage(/.*)?'
restorecon -RFvv /opt/librenms/storage/
semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/bootstrap/cache(/.*)?'
semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/bootstrap/cache(/.*)?'
restorecon -RFvv /opt/librenms/bootstrap/cache/
semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/cache(/.*)?'
semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/cache(/.*)?'
restorecon -RFvv /var/www/opt/librenms/cache/
setsebool -P httpd_can_sendmail=1
```

Additional SELinux problems may be found by executing the following command

```
audit2why < /var/log/audit/audit.log
```

# Allow fping

Create the file http_fping.tt with the following contents. You can
create this file anywhere, as it is a throw-away file. The last step
in this install procedure will install the module in the proper
location.

```
module http_fping 1.0;

require {
type httpd_t;
class capability net_raw;
class rawip_socket { getopt create setopt write read };
}

#============= httpd_t ==============
allow httpd_t self:capability net_raw;
allow httpd_t self:rawip_socket { getopt create setopt write read };
```

Then run these commands

```
checkmodule -M -m -o http_fping.mod http_fping.tt
semodule_package -o http_fping.pp -m http_fping.mod
semodule -i http_fping.pp
```

# Allow access through firewall

```
firewall-cmd --zone public --add-service http
firewall-cmd --permanent --zone public --add-service http
firewall-cmd --zone public --add-service https
firewall-cmd --permanent --zone public --add-service https
```

# Configure snmpd

Copy the example snmpd.conf from the LibreNMS install.

```
cp /opt/librenms/snmpd.conf.example /etc/snmp/snmpd.conf
```

```
vi /etc/snmp/snmpd.conf
```

Edit the text which says `RANDOMSTRINGGOESHERE` and set your own community string.

```
curl -o /usr/bin/distro https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/distro
chmod +x /usr/bin/distro
systemctl enable snmpd
systemctl restart snmpd
```

# Cron job

```
cp /opt/librenms/librenms.nonroot.cron /etc/cron.d/librenms
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
cp /opt/librenms/misc/librenms.logrotate /etc/logrotate.d/librenms
```

# Web installer

Now head to the web installer and follow the on-screen instructions.

<http://librenms.example.com/install.php>

The web installer might prompt you to create a `config.php` file in
your librenms install location manually, copying the content displayed
on-screen to the file. If you have to do this, please remember to set
the permissions on config.php after you copied the on-screen contents
to the file. Run:

```
chown librenms:librenms /opt/librenms/config.php
```

# Final steps

That's it!  You now should be able to log in to
<http://librenms.example.com/>.  Please note that we have not covered
HTTPS setup in this example, so your LibreNMS install is not secure by
default.  Please do not expose it to the public Internet unless you
have configured HTTPS and taken appropriate web server hardening
steps.

# Add the first device

We now suggest that you add localhost as your first device from within the WebUI.

# Troubleshooting

If you ever have issues with your install, run validate.php as root in
the librenms directory:

```
cd /opt/librenms
./validate.php
```

There are various options for getting help listed on the LibreNMS web
site: <https://www.librenms.org/#support>

# What next?

Now that you've installed LibreNMS, we'd suggest that you have a read
of a few other docs to get you going:

- [Performance tuning](../Support/Performance.md)
- [Alerting](../Extensions/Alerting.md)
- [Device Groups](../Extensions/Device-Groups.md)
- [Auto discovery](../Extensions/Auto-Discovery.md)

# Closing

We hope you enjoy using LibreNMS. If you do, it would be great if you
would consider opting into the stats system we have, please see [this
page](../General/Callback-Stats-and-Privacy.md) on
what it is and how to enable it.

If you would like to help make LibreNMS better there are [many ways to
help](../Support/FAQ.md#a-namefaq9-what-can-i-do-to-helpa). You
can also [back LibreNMS on Open Collective](https://t.libren.ms/donations).
