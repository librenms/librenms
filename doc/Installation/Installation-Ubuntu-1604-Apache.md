> NOTE: These instructions assume you are the root user.  If you are not, prepend `sudo` to the shell commands (the ones that aren't at `mysql>` prompts) or temporarily become a user with root privileges with `sudo -s` or `sudo -i`.

### DB Server ###

> NOTE: Whilst we are working on ensuring LibreNMS is compatible with MySQL strict mode, for now, please disable this after mysql is installed.

#### Install / Configure MySQL
```bash
apt-get install mariadb-server mariadb-client
mysql -uroot -p
```

```sql
CREATE DATABASE librenms;
GRANT ALL PRIVILEGES ON librenms.*
  TO 'librenms'@'localhost'
  IDENTIFIED BY '<password>'
;
FLUSH PRIVILEGES;
exit
```

`vim /etc/mysql/mariadb.conf.d/50-server.cnf`

Within the [mysqld] section please add:

```bash
innodb_file_per_table=1
sql-mode=""
```

```service mysql restart```

### Web Server ###

#### Install / Configure Apache

`apt-get install libapache2-mod-php7.0 php7.0-cli php7.0-mysql php7.0-gd php7.0-snmp php-pear php7.0-curl snmp graphviz php7.0-mcrypt php7.0-json apache2 fping imagemagick whois mtr-tiny nmap python-mysqldb snmpd php-net-ipv4 php-net-ipv6 rrdtool git`

In `/etc/php/7.0/apache2/php.ini` and `/etc/php/7.0/cli/php.ini`, ensure date.timezone is set to your preferred time zone.  See http://php.net/manual/en/timezones.php for a list of supported timezones.  Valid examples are: "America/New York", "Australia/Brisbane", "Etc/UTC".

```bash
a2enmod php7.0
a2dismod mpm_event
a2enmod mpm_prefork
phpenmod mcrypt
```

#### Add librenms user

```bash
useradd librenms -d /opt/librenms -M -r
usermod -a -G librenms www-data
```

#### Clone repo

```bash
cd /opt
git clone https://github.com/librenms/librenms.git librenms
```

#### Web interface

```bash
cd /opt/librenms
mkdir rrd logs
chmod 775 rrd
vim /etc/apache2/sites-available/librenms.conf
```

Add the following config:

```apache
<VirtualHost *:80>
  DocumentRoot /opt/librenms/html/
  ServerName  librenms.example.com
  CustomLog /opt/librenms/logs/access_log combined
  ErrorLog /opt/librenms/logs/error_log
  AllowEncodedSlashes NoDecode
  <Directory "/opt/librenms/html/">
    Require all granted
    AllowOverride All
    Options FollowSymLinks MultiViews
  </Directory>
</VirtualHost>
```

```bash
a2ensite librenms.conf
a2enmod rewrite
service apache2 restart
```

> NOTE: If this is the only site you are hosting on this server (it should be :)) then you will need to disable the default site.

`a2dissite 000-default`

#### Web installer

Now head to: http://librenms.example.com/install.php and follow the on-screen instructions.

#### Configure snmpd

```bash
cp /opt/librenms/snmpd.conf.example /etc/snmpd.conf
vim /etc/snmpd.conf
```

Edit the text which says `RANDOMSTRINGGOESHERE` and set your own community string.

`service snmpd restart`

#### Cron job

`cp librenms.nonroot.cron /etc/cron.d/librenms`

#### Final steps

```bash
chown librenms:librenms /opt/librenms/.git/index
chown -R librenms:librenms /opt/librenms
```

Now run validate your install and make sure everything is ok:

```bash
cd /opt/librenms
./validate.php
```

That's it!  You now should be able to log in to http://librenms.example.com/.  Please note that we have not covered HTTPS setup in this example, so your LibreNMS install is not secure by default.  Please do not expose it to the public Internet unless you have configured HTTPS and taken appropriate web server hardening steps.

#### Add first device

We now suggest that you add localhost as your first device from within the WebUI.

#### Closing

We hope you enjoy using LibreNMS. If you do, it would be great if you would consider opting into the stats system we have, please see [this page](http://docs.librenms.org/General/Callback-Stats-and-Privacy/) on what it is and how to enable it.
