source: Installation/Installation-CentOS-7-Nginx.md
> NOTE: These instructions assume you are the root user.  If you are not, prepend `sudo` to the shell commands (the ones that aren't at `mysql>` prompts) or temporarily become a user with root privileges with `sudo -s` or `sudo -i`.

### DB Server ###

> NOTE: Whilst we are working on ensuring LibreNMS is compatible with MySQL strict mode, for now, please disable this after mysql is installed.

#### Install / Configure MySQL
```bash
yum install mariadb-server mariadb
systemctl restart mariadb
mysql -uroot
```

```sql
CREATE DATABASE librenms CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
FLUSH PRIVILEGES;
exit
```

`vi /etc/my.cnf`

Within the [mysqld] section please add:

```bash
innodb_file_per_table=1
sql-mode=""
lower_case_table_names=0
```

```
systemctl enable mariadb  
systemctl restart mariadb
```

### Web Server ###

#### Install / Configure Nginx

```bash
yum install epel-release
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm

yum install php70w php70w-cli php70w-gd php70w-mysql php70w-snmp php70w-curl php70w-common php70w-fpm nginx net-snmp mariadb ImageMagick jwhois nmap mtr rrdtool MySQL-python net-snmp-utils cronie php70w-mcrypt fping git
```

In `/etc/php.ini` ensure date.timezone is set to your preferred time zone.  See http://php.net/manual/en/timezones.php for a list of supported timezones.  Valid examples are: "America/New_York", "Australia/Brisbane", "Etc/UTC".

In `/etc/php-fpm.d/www.conf` make these changes:

```nginx
;listen = 127.0.0.1:9000
listen = /var/run/php-fpm/php7.0-fpm.sock

listen.owner = nginx
listen.group = nginx
listen.mode = 0660
```
Restart PHP.

```bash
systemctl restart php-fpm
systemctl enable php-fpm
```

#### Add librenms user

```bash
useradd librenms -d /opt/librenms -M -r
usermod -a -G librenms nginx
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
vi /etc/nginx/conf.d/librenms.conf
```

Add the following config:

```nginx
server {
 listen      80;
 server_name librenms.example.com;
 root        /opt/librenms/html;
 index       index.php;
 access_log  /opt/librenms/logs/access_log;
 error_log   /opt/librenms/logs/error_log;
 charset utf-8;
 gzip on;
 gzip_types text/css application/javascript text/javascript application/x-javascript image/svg+xml text/plain text/xsd text/xsl text/xml image/x-icon;
 location / {
  try_files $uri $uri/ /index.php?$query_string;
 }
 location /api/v0 {
  try_files $uri $uri/ /api_v0.php?$query_string;
 }
 location ~ \.php {
  include fastcgi.conf;
  fastcgi_split_path_info ^(.+\.php)(/.+)$;
  fastcgi_pass unix:/var/run/php-fpm/php7.0-fpm.sock;
  fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
 }
 location ~ /\.ht {
  deny all;
 }
}
```

If LibreNMS will be your only vhost on this server then you will need to remove the default server block for nginx.

Edit `/etc/nginx/nginx.conf` and look for a large block of text starting like:

```
server {
        listen       80;
        server_name  localhost;
...
}
```

Remove this block of text.

#### SELinux

```bash
    yum install policycoreutils-python
    semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/logs(/.*)?'
    semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/logs(/.*)?'
    semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/rrd(/.*)?'
    semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/rrd(/.*)?'
    restorecon -RFvv /opt/librenms/logs/
    restorecon -RFvv /opt/librenms/rrd/
    setsebool -P httpd_can_sendmail=1
```

#### Allow access through firewall

```bash
firewall-cmd --zone public --add-service http
firewall-cmd --permanent --zone public --add-service http
```

#### Restart Web server

```bash
systemctl restart nginx
```

#### Web installer

Now head to: http://librenms.example.com/install.php and follow the on-screen instructions.

Once you have completed the web installer steps. Please add the following to `config.php`

`$config['fping'] = "/usr/sbin/fping";`

#### Configure snmpd

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

#### Cron job

`cp librenms.nonroot.cron /etc/cron.d/librenms`

#### Copy logrotate config

LibreNMS keeps logs in `/opt/librenms/logs`. Over time these can become large and be rotated out.  To rotate out the old logs you can use the provided logrotate config file:

    cp misc/librenms.logrotate /etc/logrotate.d/librenms

#### Final steps

```bash
chown -R librenms:librenms /opt/librenms
systemctl enable nginx mariadb
```

Run validate.php as root in the librenms directory:

```bash
cd /opt/librenms
./validate.php
```

That's it!  You now should be able to log in to http://librenms.example.com/.  Please note that we have not covered HTTPS setup in this example, so your LibreNMS install is not secure by default.  Please do not expose it to the public Internet unless you have configured HTTPS and taken appropriate web server hardening steps.

#### Add first device

We now suggest that you add localhost as your first device from within the WebUI.

#### What next?

Now that you've installed LibreNMS, we'd suggest that you have a read of a few other docs to get you going:

 - [Performance tuning](http://docs.librenms.org/Support/Performance)
 - [Alerting](http://docs.librenms.org/Extensions/Alerting/)
 - [Device Groups](http://docs.librenms.org/Extensions/Device-Groups/)
 - [Auto discovery](http://docs.librenms.org/Extensions/Auto-Discovery/)

#### Closing

We hope you enjoy using LibreNMS. If you do, it would be great if you would consider opting into the stats system we have, please see [this page](http://docs.librenms.org/General/Callback-Stats-and-Privacy/) on what it is and how to enable it.
