source: Installation/Installation-Debian-10-Nginx.md
path: blob/master/doc/

> NOTE: These instructions assume you are the **root** user.  If you
> are not, prepend `sudo` to the shell commands (the ones that aren't
> at `mysql>` prompts) or temporarily become a user with root
> privileges with `sudo -s` or `sudo -i`.

**Please note the minimum supported PHP version is @= php.version_min =@**

# Install Required Packages

```bash
apt install acl curl composer fping git graphviz imagemagick mariadb-client mariadb-server mtr-tiny nginx-full nmap php7.3-cli php7.3-curl php7.3-fpm php7.3-gd php7.3-json php7.3-mbstring php7.3-mysql php7.3-snmp php7.3-xml php7.3-zip python-memcache python-mysqldb rrdtool snmp snmpd whois python3-pymysql python3-dotenv python3-redis python3-setuptools
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
service mysql restart
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
nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

Within the `[mysqld]` section please add:

```bash
innodb_file_per_table=1
lower_case_table_names=0
```

```bash
service mysql restart
```

# Web Server

## Configure and Start PHP-FPM

Ensure date.timezone is set in php.ini to your preferred time zone.
See <https://php.net/manual/en/timezones.php> for a list of supported
timezones.  Valid examples are: "America/New_York",
"Australia/Brisbane", "Etc/UTC".

```bash
nano /etc/php/7.3/fpm/php.ini
nano /etc/php/7.3/cli/php.ini
```

```bash
service php7.3-fpm restart
```

## Configure NGINX

```bash
rm /etc/nginx/sites-enabled/default
nano /etc/nginx/sites-available/librenms.vhost
```

Add the following config, edit `server_name` as required:

```nginx
server {
 listen      80;
 server_name librenms.example.com;
 root        /opt/librenms/html;
 index       index.php;

 charset utf-8;
 gzip on;
 gzip_types text/css application/javascript text/javascript application/x-javascript image/svg+xml text/plain text/xsd text/xsl text/xml image/x-icon;

 proxy_read_timeout 300;
 proxy_connect_timeout 300;
 proxy_send_timeout 300;

 location / {
  try_files $uri $uri/ /index.php?$query_string;
 }
 location /api/v0 {
  try_files $uri $uri/ /api_v0.php?$query_string;
 }
 location ~ \.php {
  include fastcgi.conf;
  fastcgi_split_path_info ^(.+\.php)(/.+)$;
  fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
 }
 location ~ /\.ht {
  deny all;
 }
}
```

```bash
ln -s /etc/nginx/sites-available/librenms.vhost /etc/nginx/sites-enabled/librenms.vhost
service nginx reload
```

# Configure snmpd

```bash
cp /opt/librenms/snmpd.conf.example /etc/snmp/snmpd.conf
chmod 600 /etc/snmp/snmpd.conf
nano /etc/snmp/snmpd.conf
```

Edit the text which says `RANDOMSTRINGGOESHERE` and set your own community string.

```bash
curl -o /usr/bin/distro https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/distro
chmod +x /usr/bin/distro
service snmpd restart
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

<http://librenms.example.com/install.php>

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

We now suggest that you add localhost as your first device from within
the WebUI.

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
