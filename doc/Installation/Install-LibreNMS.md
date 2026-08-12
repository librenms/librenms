# Install LibreNMS

## Prepare Linux Server

You should have an installed Linux server running one of the supported OS.
Make sure you select your server's OS in the tabbed options below.
Choice of web server is your preference, NGINX is recommended.

Connect to the server command line and follow the instructions below.
!!! note

    These instructions assume you are the **root** user.  
    If you are not, prepend `sudo` to the shell commands (the ones that aren't
    at `mysql>` prompts) or temporarily become a user with root
    privileges with `sudo -s` or `sudo -i`.

**Please note the minimum supported PHP version is @= php.version_min =@, the recommended version is @= php.version_recommended =@**

## Install Required Packages

=== "Ubuntu 26.04"
    === "NGINX"
        ```
        apt install acl curl fping git mariadb-client mariadb-server mtr-tiny nginx-full nmap php-cli php-curl php-fpm php-gd php-gmp php-json php-mbstring php-mysql php-snmp php-xml php-zip python3-command-runner python3-dotenv python3-pip python3-psutil python3-pymysql python3-redis python3-setuptools python3-systemd rrdtool snmp snmpd traceroute unzip whois
        ```

=== "Ubuntu 24.04"
    === "NGINX"
        ```
        apt install lsb-release ca-certificates curl
        curl -sSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb
        dpkg -i /tmp/debsuryorg-archive-keyring.deb
        echo "deb [signed-by=/usr/share/keyrings/debsuryorg-archive-keyring.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
        apt update
        apt install acl curl fping git mariadb-client mariadb-server mtr-tiny nginx-full nmap php@= php.version_recommended =@-cli php@= php.version_recommended =@-curl php@= php.version_recommended =@-fpm php@= php.version_recommended =@-gd php@= php.version_recommended =@-gmp php@= php.version_recommended =@-mbstring php@= php.version_recommended =@-mysql php@= php.version_recommended =@-snmp php@= php.version_recommended =@-xml php@= php.version_recommended =@-zip python3-command-runner python3-dotenv python3-pip python3-psutil python3-pymysql python3-redis python3-setuptools python3-systemd rrdtool snmp snmpd traceroute unzip whois
        ```

=== "Debian 12"
    === "NGINX"
        ```
        apt install lsb-release ca-certificates curl
        curl -sSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb
        dpkg -i /tmp/debsuryorg-archive-keyring.deb
        echo "deb [signed-by=/usr/share/keyrings/debsuryorg-archive-keyring.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
        apt update
        apt install acl curl fping git mariadb-client mariadb-server mtr-tiny nginx-full nmap php@= php.version_recommended =@-cli php@= php.version_recommended =@-curl php@= php.version_recommended =@-fpm php@= php.version_recommended =@-gd php@= php.version_recommended =@-gmp php@= php.version_recommended =@-mbstring php@= php.version_recommended =@-mysql php@= php.version_recommended =@-snmp php@= php.version_recommended =@-xml php@= php.version_recommended =@-zip python3-dotenv python3-pip python3-psutil python3-pymysql python3-redis python3-setuptools python3-systemd rrdtool snmp snmpd unzip whois
        ```

=== "Debian 13"
    === "NGINX"
        ```
        apt install acl ca-certificates curl fping git lsb-release mariadb-client mariadb-server mtr-tiny nginx-full nmap php-cli php-curl php-fpm php-gd php-gmp php-mbstring php-mysql php-snmp php-xml php-zip python3-command-runner python3-dotenv python3-pip python3-psutil python3-pymysql python3-redis python3-setuptools python3-systemd rrdtool snmp snmpd unzip wget whois
        ```

## Add librenms user

```
useradd librenms -d /opt/librenms -M -r -s "$(which bash)"
```

## Download LibreNMS

```
cd /opt
git clone https://github.com/librenms/librenms.git
```

## Set permissions

```
chown -R librenms:librenms /opt/librenms
chmod 771 /opt/librenms
setfacl -d -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/
setfacl -R -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/
```

## Install PHP dependencies

Change to the LibreNMS user:
```
su - librenms
```

Then run the composer wrapper script and exit back to the root user:
```
./scripts/composer_wrapper.php install --no-dev
exit
```

!!! note
    Sometimes when there is a proxy used to gain internet access, the above script may fail.
    The workaround is to install the `composer` package manually. For a global installation:
    ```
    wget https://getcomposer.org/composer-stable.phar
    mv composer-stable.phar /usr/bin/composer
    chmod +x /usr/bin/composer
    ```

## Set timezone

See <https://php.net/manual/en/timezones.php> for a list of supported
timezones.  Valid examples are: "America/New_York", "Australia/Brisbane", "Etc/UTC".
Ensure date.timezone is set in php.ini to your preferred time zone.

=== "Ubuntu 26.04"
    ```bash
    vi /etc/php/8.5/fpm/php.ini
    vi /etc/php/8.5/cli/php.ini
    ```

=== "Ubuntu 24.04"
    ```bash
    vi /etc/php/@= php.version_recommended =@/fpm/php.ini
    vi /etc/php/@= php.version_recommended =@/cli/php.ini
    ```

=== "Debian 12"
    ```bash
    vi /etc/php/@= php.version_recommended =@/fpm/php.ini
    vi /etc/php/@= php.version_recommended =@/cli/php.ini
    ```

=== "Debian 13"
    ```bash
    vi /etc/php/8.4/fpm/php.ini
    vi /etc/php/8.4/cli/php.ini
    ```

Remember to set the system timezone as well.

```
timedatectl set-timezone Etc/UTC
```


## Configure MariaDB

=== "Ubuntu 26.04"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```
    
    Within the `[mariadbd]` section add:

    ```
    innodb_file_per_table=1
    lower_case_table_names=0
    ```

=== "Ubuntu 24.04"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```

    Within the `[mysqld]` section add:

    ```
    innodb_file_per_table=1
    lower_case_table_names=0
    ```

=== "Debian 12"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```

    Within the `[mysqld]` section add:

    ```
    innodb_file_per_table=1
    lower_case_table_names=0
    ```    

=== "Debian 13"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```

    Within the `[mariadbd]` section add:

    ```
    innodb_file_per_table=1
    lower_case_table_names=0
    ```


Then restart MariaDB

```
systemctl enable mariadb
systemctl restart mariadb
```
Start MariaDB client

```
mysql -u root
```

!!! warning
    Change the 'password' below to something secure.

```sql
CREATE DATABASE librenms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
exit
```

## Configure PHP-FPM

=== "Ubuntu 26.04"
    ```bash
    cp /etc/php/8.5/fpm/pool.d/www.conf /etc/php/8.5/fpm/pool.d/librenms.conf
    vi /etc/php/8.5/fpm/pool.d/librenms.conf
    ```

=== "Ubuntu 24.04"
    ```bash
    cp /etc/php/@= php.version_recommended =@/fpm/pool.d/www.conf /etc/php/@= php.version_recommended =@/fpm/pool.d/librenms.conf
    vi /etc/php/@= php.version_recommended =@/fpm/pool.d/librenms.conf
    ```

=== "Debian 12"
    ```bash
    cp /etc/php/@= php.version_recommended =@/fpm/pool.d/www.conf /etc/php/@= php.version_recommended =@/fpm/pool.d/librenms.conf
    vi /etc/php/@= php.version_recommended =@/fpm/pool.d/librenms.conf
    ```

=== "Debian 13"
    ```bash
    cp /etc/php/8.4/fpm/pool.d/www.conf /etc/php/8.4/fpm/pool.d/librenms.conf
    vi /etc/php/8.4/fpm/pool.d/librenms.conf
    ```

Change `[www]` to `[librenms]`:
```
[librenms]
```

Change `user` and `group` to "librenms":
```
user = librenms
group = librenms
```

Change `listen` to a unique path that must match your webserver's config (`fastcgi_pass` for NGINX and `SetHandler` for Apache) :
```
listen = /run/php-fpm-librenms.sock
```

If there are no other PHP web applications on this server, you may remove www.conf to save some resources.
Feel free to tune the performance settings in librenms.conf to meet your needs.

## Configure Web Server

=== "Ubuntu 26.04"
    === "NGINX"
        ```bash
        vi /etc/nginx/conf.d/librenms.conf
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
         location / {
          try_files $uri $uri/ /index.php?$query_string;
         }
         location ~ [^/]\.php(/|$) {
          fastcgi_pass unix:/run/php-fpm-librenms.sock;
          fastcgi_split_path_info ^(.+\.php)(/.+)$;
          include fastcgi.conf;
         }
         location ~ /\.(?!well-known).* {
          deny all;
         }
        }
        ```

        ```bash
        rm /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default
        systemctl restart nginx
        systemctl restart php8.5-fpm
        ```

=== "Ubuntu 24.04"
    === "NGINX"
        ```bash
        vi /etc/nginx/conf.d/librenms.conf
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
         location / {
          try_files $uri $uri/ /index.php?$query_string;
         }
         location ~ [^/]\.php(/|$) {
          fastcgi_pass unix:/run/php-fpm-librenms.sock;
          fastcgi_split_path_info ^(.+\.php)(/.+)$;
          include fastcgi.conf;
         }
         location ~ /\.(?!well-known).* {
          deny all;
         }
        }
        ```

        ```bash
        rm /etc/nginx/sites-enabled/default
        systemctl restart nginx
        systemctl restart php@= php.version_recommended =@-fpm
        ```

=== "Debian 12"
    === "NGINX"
        ```bash
        vi /etc/nginx/sites-enabled/librenms.vhost
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
         location / {
          try_files $uri $uri/ /index.php?$query_string;
         }
         location ~ [^/]\.php(/|$) {
          fastcgi_pass unix:/run/php-fpm-librenms.sock;
          fastcgi_split_path_info ^(.+\.php)(/.+)$;
          include fastcgi.conf;
         }
         location ~ /\.(?!well-known).* {
          deny all;
         }
        }
        ```

        ```bash
        rm /etc/nginx/sites-enabled/default
        systemctl reload nginx
        systemctl restart php@= php.version_recommended =@-fpm
        ```

=== "Debian 13"
    === "NGINX"
        ```bash
        vi /etc/nginx/sites-enabled/librenms.vhost
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
         location / {
          try_files $uri $uri/ /index.php?$query_string;
         }
         location ~ [^/]\.php(/|$) {
          fastcgi_pass unix:/run/php-fpm-librenms.sock;
          fastcgi_split_path_info ^(.+\.php)(/.+)$;
          include fastcgi.conf;
         }
         location ~ /\.(?!well-known).* {
          deny all;
         }
        }
        ```

        ```bash
        rm /etc/nginx/sites-enabled/default
        systemctl reload nginx
        systemctl restart php8.4-fpm
        ```

## SELinux

=== "Ubuntu 26.04"
    SELinux not enabled by default

=== "Ubuntu 24.04"
    SELinux not enabled by default

=== "Debian 12"
    SELinux not enabled by default

=== "Debian 13"
    SELinux not enabled by default

## Allow access through firewall
=== "Ubuntu 26.04"
    Firewall not enabled by default

=== "Ubuntu 24.04"
    Firewall not enabled by default

=== "Debian 12"
    Firewall not enabled by default

=== "Debian 13"
    Firewall not enabled by default

## Enable lnms command completion

This feature grants you the opportunity to use tab for completion on lnms commands as you would
for normal linux commands.

```
ln -s /opt/librenms/lnms /usr/bin/lnms
cp /opt/librenms/misc/lnms-completion.bash /etc/bash_completion.d/
```

`lnms config` is the preferred method for [Configuration](../Support/Configuration.md)


## Configure snmpd (v2c)

If you would like to use SNMPv3 then please [see here](../Support/SNMP-Configuration-Examples.md/#linux-snmpd-v3)

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

## Cron job

```
cp /opt/librenms/dist/librenms.cron /etc/cron.d/librenms
```

!!! note
    Keep in mind  that cron, by default, only uses a very limited
    set of environment variables. You may need to configure proxy
    variables for the cron invocation. Alternatively adding the proxy
    settings in config.php is possible too. The config.php file will be
    created in the upcoming steps. Review the following URL after you
    finished librenms install steps:
    <@= config.site_url =@/Support/Configuration/#proxy-support>

## Enable the scheduler

```
cp /opt/librenms/dist/librenms-scheduler.service /opt/librenms/dist/librenms-scheduler.timer /etc/systemd/system/

systemctl enable librenms-scheduler.timer
systemctl start librenms-scheduler.timer
```

## Enable logrotate

LibreNMS keeps logs in `/opt/librenms/logs`. Over time these can
become large and be rotated out.  To rotate out the old logs you can
use the provided logrotate config file:

```
cp /opt/librenms/misc/librenms.logrotate /etc/logrotate.d/librenms
```

## Web installer

Now head to the web installer and follow the on-screen instructions.

<http://librenms.example.com/install>

The web installer might prompt you to create a `config.php` file in
your librenms install location manually, copying the content displayed
on-screen to the file. If you have to do this, please remember to set
the permissions on config.php after you copied the on-screen contents
to the file. Run:

```
chown librenms:librenms /opt/librenms/config.php
```

## Final steps

That's it!  You now should be able to log in to
<http://librenms.example.com/>.

!!! danger "Security & Backup Warning"
    - Please note that we have not covered HTTPS setup in this example, so your LibreNMS install is not secure by default. Please do not expose it to the public Internet unless you have configured HTTPS and taken appropriate web server hardening steps.
    - **Back up your `.env` file!** Your `.env` contains the `APP_KEY`, which is the master encryption key used to protect secrets and credentials stored in the database. If you lose your `APP_KEY`, encrypted data will be unrecoverable from backups or during server migrations.


## Add the first device

We now suggest that you add localhost as your first device from within the WebUI.
<https://librenms.example.com/addhost>

## Troubleshooting

If you ever have issues with your install, you should run validate which will perform
some base checks and provide the recommended fixes:

```
sudo su - librenms
./validate.php
```

There are various options for getting help listed on the LibreNMS web
site: <https://www.librenms.org/#support>

## What next?

Now that you've installed LibreNMS, we'd suggest that you have a read
of a few other docs to get you going:

- [Performance tuning](../Support/Performance.md)
- [Alerting](../Alerting/index.md)
- [Device Groups](../Extensions/Device-Groups.md)
- [Auto discovery](../Extensions/Auto-Discovery.md)
- [High Availability](../Support/High-Availability.md)

## Closing

We hope you enjoy using LibreNMS. If you do, it would be great if you
would consider opting into the stats system we have, please see [this
page](../General/Callback-Stats-and-Privacy.md) on
what it is and how to enable it.

If you would like to help make LibreNMS better there are [many ways to
help](../Support/FAQ.md#faq9). You
can also [back LibreNMS on Open Collective](https://t.libren.ms/donations).
