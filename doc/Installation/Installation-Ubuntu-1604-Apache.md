source: Installation/Installation-Ubuntu-1604-Apache.md
path: blob/master/doc/

> NOTE: These instructions assume you are the **root** user.  If you
> are not, prepend `sudo` to the shell commands (the ones that aren't
> at `mysql>` prompts) or temporarily become a user with root
> privileges with `sudo -s` or `sudo -i`.

**Please note the minimum supported PHP version is 5.6.4**

# Install Required Packages

    apt install acl apache2 composer fping git graphviz imagemagick libapache2-mod-php7.0 mariadb-client mariadb-server mtr-tiny nmap php7.0-cli php7.0-curl php7.0-gd php7.0-json php7.0-mbstring php7.0-mcrypt php7.0-mysql php7.0-snmp php7.0-xml php7.0-zip python-memcache python-mysqldb rrdtool snmp snmpd whois


# Add librenms user

    useradd librenms -d /opt/librenms -M -r
    usermod -a -G librenms www-data

# Install LibreNMS

    cd /opt
    composer create-project --no-dev --keep-vcs librenms/librenms librenms dev-master


# DB Server

## Configure MySQL

```
systemctl restart mysql
mysql -uroot -p
```

> NOTE: Please change the 'password' below to something secure.

```sql
CREATE DATABASE librenms CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
FLUSH PRIVILEGES;
exit
```

```
vi /etc/mysql/mariadb.conf.d/50-server.cnf
```

Within the `[mysqld]` section please add:

```bash
innodb_file_per_table=1
lower_case_table_names=0
```

```
systemctl restart mysql
```

# Web Server

## Configure PHP

Ensure date.timezone is set in php.ini to your preferred time zone.
See <http://php.net/manual/en/timezones.php> for a list of supported
timezones.  Valid examples are: "America/New_York",
"Australia/Brisbane", "Etc/UTC".

```
vi /etc/php/7.0/apache2/php.ini
vi /etc/php/7.0/cli/php.ini
```

```
a2enmod php7.0
a2dismod mpm_event
a2enmod mpm_prefork
phpenmod mcrypt
```

## Configure Apache

```
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

```
a2ensite librenms.conf
a2enmod rewrite
systemctl restart apache2
```

# Configure snmpd

```
cp /opt/librenms/snmpd.conf.example /etc/snmp/snmpd.conf
vi /etc/snmp/snmpd.conf
```

Edit the text which says `RANDOMSTRINGGOESHERE` and set your own community string.

```
curl -o /usr/bin/distro https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/distro
chmod +x /usr/bin/distro
systemctl restart snmpd
```

## Cron job

```
cp /opt/librenms/librenms.nonroot.cron /etc/cron.d/librenms
```

> NOTE: Keep in mind  that cron, by default, only uses a very limited
> set of environment variables. You may need to configure proxy
> variables for the cron invocation. Alternatively adding the proxy
> settings in config.php is possible too. The config.php file will be
> created in the upcoming steps. Review the following URL after you
> finished librenms install steps:
> <https://docs.librenms.org/Support/Configuration/#proxy-support>

# Copy logrotate config

LibreNMS keeps logs in `/opt/librenms/logs`. Over time these can
become large and be rotated out.  To rotate out the old logs you can
use the provided logrotate config file:

```
cp /opt/librenms/misc/librenms.logrotate /etc/logrotate.d/librenms
```

# Set permissions

```
chown -R librenms:librenms /opt/librenms
setfacl -d -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/
setfacl -R -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/
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

- [Performance tuning](http://docs.librenms.org/Support/Performance)
- [Alerting](http://docs.librenms.org/Extensions/Alerting/)
- [Device Groups](http://docs.librenms.org/Extensions/Device-Groups/)
- [Auto discovery](http://docs.librenms.org/Extensions/Auto-Discovery/)

# Closing

We hope you enjoy using LibreNMS. If you do, it would be great if you
would consider opting into the stats system we have, please see [this
page](http://docs.librenms.org/General/Callback-Stats-and-Privacy/) on
what it is and how to enable it.

If you would like to help make LibreNMS better there are [many ways to
help](http://docs.librenms.org/Support/FAQ/#what-can-i-do-to-help). You
can also [back LibreNMS on Open
Collective](https://t.libren.ms/donations).
