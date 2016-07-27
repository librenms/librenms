> NOTE: These instructions assume you are the root user.  If you are not, prepend `sudo` to the shell commands (the ones that aren't at `mysql>` prompts) or temporarily become a user with root privileges with `sudo -s` or `sudo -i`.

### DB Server ###

> NOTE: Whilst we are working on ensuring LibreNMS is compatible with MySQL strict mode, for now, please disable this after mysql is installed.

#### Install / Configure MySQL
```bash
yum install mariadb-server mariadb
service mariadb restart
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

`vim /etc/my.cnf.d/server.cnf`

Within the [mysqld] section please add:

```bash
innodb_file_per_table=1
sql-mode=""
```

```service mariadb restart```

### Web Server ###

#### Install / Configure Apache

```bash
yum install epel-release
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm

yum install php70w php70w-cli php70w-gd php70w-mysql php70w-snmp php70w-pear php70w-curl php70w-common php70w-fpm nginx net-snmp mariadb ImageMagick jwhois nmap mtr rrdtool MySQL-python net-snmp-utils cronie php70w-mcrypt fping git

pear install Net_IPv4-1.3.4
pear install Net_IPv6-1.2.2b2
```

In `/etc/php.ini` ensure date.timezone is set to your preferred time zone.  See http://php.net/manual/en/timezones.php for a list of supported timezones.  Valid examples are: "America/New York", "Australia/Brisbane", "Etc/UTC".

#### Add librenms user

```bash
useradd librenms -d /opt/librenms -M -r
usermod -a -G librenms apache
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
vim /etc/httpd/conf.d/librenms.conf
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

> NOTE: If this is the only site you are hosting on this server (it should be :)) then you will need to disable the default site.

`rm -f /etc/httpd/conf.d/welcome.conf`

#### SELinux

```bash
    yum install policycoreutils-python
    semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/logs(/.*)?'
    semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/logs(/.*)?'
    restorecon -RFvv /opt/librenms/logs/
    setsebool -P httpd_can_sendmail=1
```

#### Restart Web server

```bash
service httpd restart
```

#### Web installer

Now head to: http://librenms.example.com/install.php and follow the on-screen instructions.

Once you have completed the web installer steps. Please add the following to `config.php`

`$config['fping'] = "/usr/sbin/fping";`

#### Configure snmpd

```bash
cp /opt/librenms/snmpd.conf.example /etc/snmpd/snmpd.conf
vim /etc/snmpd/snmpd.conf
```

Edit the text which says `RANDOMSTRINGGOESHERE` and set your own community string.

`service snmpd restart`

#### Cron job

`cp librenms.nonroot.cron /etc/cron.d/librenms`

#### Final steps

```bash
chown -R librenms:librenms /opt/librenms
systemctl enable httpd
systemctl enable mariadb
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
