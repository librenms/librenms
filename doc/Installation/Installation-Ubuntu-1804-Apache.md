source: Installation/Installation-Ubuntu-1804-Apache.md
path: blob/master/doc/

> NOTE: These instructions assume you are the **root** user.  If you
> are not, prepend `sudo` to the shell commands (the ones that aren't
> at `mysql>` prompts) or temporarily become a user with root
> privileges with `sudo -s` or `sudo -i`.

**Please note the minimum supported PHP version is @= config.extra.php_min_version =@**

# Install Required Packages

```bash
apt install software-properties-common
add-apt-repository universe
apt update
apt install curl apache2 composer fping git graphviz imagemagick libapache2-mod-php7.2 mariadb-client mariadb-server mtr-tiny nmap php7.2-cli php7.2-curl php7.2-gd php7.2-json php7.2-mbstring php7.2-mysql php7.2-snmp php7.2-xml php7.2-zip python-memcache python-mysqldb rrdtool snmp snmpd whois python3-pip
```

# Add librenms user

```bash
useradd librenms -d /opt/librenms -M -r
usermod -a -G librenms www-data
```

# Download LibreNMS

```bash
cd /opt
git clone https://github.com/librenms/librenms.git
```

# Set permissions

```bash
chown -R librenms:librenms /opt/librenms
chmod 770 /opt/librenms
setfacl -d -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/
setfacl -R -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/
```

# Install PHP dependencies

```bash
su - librenms
./scripts/composer_wrapper.php install --no-dev
exit
```

# DB Server

## Configure MySQL

```bash
systemctl restart mysql
mysql -uroot -p
```

> NOTE: Please change the 'password' below to something secure.

```sql
CREATE DATABASE librenms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
FLUSH PRIVILEGES;
exit
```

```bash
vi /etc/mysql/mariadb.conf.d/50-server.cnf
```

Within the `[mysqld]` section please add:

```bash
innodb_file_per_table=1
lower_case_table_names=0
```

```bash
systemctl restart mysql
```

# Web Server

## Configure PHP

Ensure date.timezone is set in php.ini to your preferred time zone.
See <https://php.net/manual/en/timezones.php> for a list of supported
timezones.  Valid examples are: "America/New_York",
"Australia/Brisbane", "Etc/UTC".

```bash
vi /etc/php/7.2/apache2/php.ini
vi /etc/php/7.2/cli/php.ini

a2enmod php7.2
a2dismod mpm_event
a2enmod mpm_prefork
```

## Configure Apache

```bash
vi /etc/apache2/sites-available/librenms.conf
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
> should be :)) then you will need to disable the default
> site. `a2dissite 000-default`

```bash
a2ensite librenms.conf
a2enmod rewrite
systemctl restart apache2
```

# Configure snmpd

```bash
cp /opt/librenms/snmpd.conf.example /etc/snmp/snmpd.conf
vi /etc/snmp/snmpd.conf
```

Edit the text which says `RANDOMSTRINGGOESHERE` and set your own community string.

```bash
curl -o /usr/bin/distro https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/distro
chmod +x /usr/bin/distro
systemctl restart snmpd
```

# Cron job

```bash
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

```bash
cp /opt/librenms/misc/librenms.logrotate /etc/logrotate.d/librenms
```

# Web installer

Now head to the web installer and follow the on-screen instructions.

```
http://librenms.example.com/install.php
```

The web installer might prompt you to create a `config.php` file in
your librenms install location manually, copying the content displayed
on-screen to the file. If you have to do this, please remember to set
the permissions on config.php after you copied the on-screen contents
to the file. Run:

```bash
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

```bash
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
