source: Installation/Install-LibreNMS.md
path: blob/master/doc/

# Prepare Linux Server

You should have an installed Linux server running one of the supported OS.
Make sure you select your server's OS in the tabbed options below.
Choice of web server is your preference, NGINX is recommended.

Connect to the server command line and follow the instructions below.

> NOTE: These instructions assume you are the **root** user.  If you
> are not, prepend `sudo` to the shell commands (the ones that aren't
> at `mysql>` prompts) or temporarily become a user with root
> privileges with `sudo -s` or `sudo -i`.

**Please note the minimum supported PHP version is @= php.version_min =@**

## Install Required Packages

=== "Ubuntu 20.04"
    === "NGINX"
        ```
        apt install software-properties-common
        add-apt-repository universe
        apt update
        apt install acl curl composer fping git graphviz imagemagick mariadb-client mariadb-server mtr-tiny nginx-full nmap php7.4-cli php7.4-curl php7.4-fpm php7.4-gd php7.4-json php7.4-mbstring php7.4-mysql php7.4-snmp php7.4-xml php7.4-zip rrdtool snmp snmpd whois unzip python3-pymysql python3-dotenv python3-redis python3-setuptools python3-systemd
        ```

    === "Apache"
        ```
        apt install software-properties-common
        add-apt-repository universe
        apt update
        apt install acl curl apache2 composer fping git graphviz imagemagick libapache2-mod-fcgid mariadb-client mariadb-server mtr-tiny nmap php7.4-cli php7.4-curl php7.4-fpm php7.4-gd php7.4-json php7.4-mbstring php7.4-mysql php7.4-snmp php7.4-xml php7.4-zip rrdtool snmp snmpd whois python3-pymysql python3-dotenv python3-redis python3-setuptools python3-systemd
        ```

=== "CentOS 8"
    === "NGINX"
        ```
        dnf -y install epel-release
        dnf module reset php
        dnf module enable php:7.3
        dnf install bash-completion cronie fping git ImageMagick mariadb-server mtr net-snmp net-snmp-utils nginx nmap php-fpm php-cli php-common php-curl php-gd php-json php-mbstring php-process php-snmp php-xml php-zip php-mysqlnd python3 python3-PyMySQL python3-redis python3-memcached python3-pip python3-systemd rrdtool unzip
        ```

    === "Apache"
        ```
        dnf -y install epel-release
        dnf module reset php
        dnf module enable php:7.3
        dnf install bash-completion cronie fping git httpd ImageMagick mariadb-server mtr net-snmp net-snmp-utils nmap php-fpm php-cli php-common php-curl php-gd php-json php-mbstring php-process php-snmp php-xml php-zip php-mysqlnd python3 python3-PyMySQL python3-redis python3-memcached python3-pip python3-systemd rrdtool unzip
        ```

=== "Debian 10"
    === "NGINX"
        ```
        apt install acl curl composer fping git graphviz imagemagick mariadb-client mariadb-server mtr-tiny nginx-full nmap php7.3-cli php7.3-curl php7.3-fpm php7.3-gd php7.3-json php7.3-mbstring php7.3-mysql php7.3-snmp php7.3-xml php7.3-zip python3-dotenv python3-pymysql python3-redis python3-setuptools python3-systemd rrdtool snmp snmpd whois
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

```
su - librenms
./scripts/composer_wrapper.php install --no-dev
exit
```
Sometime when there is a proxy used to gain internet access, the above script may fail. The workaround is to install the `composer` package manually. For a global installation:
```
wget https://getcomposer.org/composer-stable.phar
mv composer-stable.phar /usr/bin/composer
chmod +x /usr/bin/composer
```

## Set timezone

See <https://php.net/manual/en/timezones.php> for a list of supported
timezones.  Valid examples are: "America/New_York", "Australia/Brisbane", "Etc/UTC".
Ensure date.timezone is set in php.ini to your preferred time zone.

=== "Ubuntu 20.04"
    ```bash
    vi /etc/php/7.4/fpm/php.ini
    vi /etc/php/7.4/cli/php.ini
    ```

=== "CentOS 8"
    ```
    vi /etc/php.ini
    ```

=== "Debian 10"
    ```bash
    vi /etc/php/7.3/fpm/php.ini
    vi /etc/php/7.3/cli/php.ini
    ```

Remember to set the system timezone as well.

```
timedatectl set-timezone Etc/UTC
```


## Configure MariaDB

=== "Ubuntu 20.04"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```

=== "CentOS 8"
    ```
    vi /etc/my.cnf.d/mariadb-server.cnf
    ```

=== "Debian 10"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```

Within the `[mysqld]` section add:

```
innodb_file_per_table=1
lower_case_table_names=0
```

```
systemctl enable mariadb
systemctl restart mariadb
```

```
mysql -u root
```

> NOTE: Change the 'password' below to something secure.

```sql
CREATE DATABASE librenms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
FLUSH PRIVILEGES;
exit
```

## Configure PHP-FPM

=== "Ubuntu 20.04"
    ```bash
    cp /etc/php/7.4/fpm/pool.d/www.conf /etc/php/7.4/fpm/pool.d/librenms.conf
    vi /etc/php/7.4/fpm/pool.d/librenms.conf
    ```

=== "CentOS 8"
    ```bash
    cp /etc/php-fpm.d/www.conf /etc/php-fpm.d/librenms.conf
    vi /etc/php-fpm.d/librenms.conf
    ```

=== "Debian 10"
    ```bash
    cp /etc/php/7.3/fpm/pool.d/www.conf /etc/php/7.3/fpm/pool.d/librenms.conf
    vi /etc/php/7.3/fpm/pool.d/librenms.conf
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

Change `listen` to a unique name:
```
listen = /run/php-fpm-librenms.sock
```

If there are no other PHP web applications on this server, you may remove www.conf to save some resources.
Feel free to tune the performance settings in librenms.conf to meet your needs.

## Configure Web Server

=== "Ubuntu 20.04"
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
        systemctl restart php7.4-fpm
        ```

    === "Apache"
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

          # Enable http authorization headers
          <IfModule setenvif_module>
            SetEnvIfNoCase ^Authorization$ "(.+)" HTTP_AUTHORIZATION=$1
          </IfModule>

          <FilesMatch ".+\.php$">
            SetHandler "proxy:unix:/run/php-fpm-librenms.sock|fcgi://localhost"
          </FilesMatch>
        </VirtualHost>
        ```

        ```bash
        a2dissite 000-default
        a2enmod proxy_fcgi setenvif rewrite
        a2ensite librenms.conf
        systemctl restart apache2
        systemctl restart php7.4-fpm
        ```

=== "CentOS 8"
    === "NGINX"
        ```
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

        > NOTE: If this is the only site you are hosting on this server (it
        > should be :)) then you will need to disable the default site.

        Delete the `server` section from `/etc/nginx/nginx.conf`

        ```
        systemctl enable --now nginx
        systemctl enable --now php-fpm
        ```

    === "Apache"
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

          # Enable http authorization headers
          <IfModule setenvif_module>
            SetEnvIfNoCase ^Authorization$ "(.+)" HTTP_AUTHORIZATION=$1
          </IfModule>

          <FilesMatch ".+\.php$">
            SetHandler "proxy:unix:/run/php-fpm-librenms.sock|fcgi://localhost"
          </FilesMatch>
        </VirtualHost>
        ```

        > NOTE: If this is the only site you are hosting on this server (it
        > should be :)) then you will need to disable the default site. `rm -f /etc/httpd/conf.d/welcome.conf`

        ```
        systemctl enable --now httpd
        systemctl enable --now php-fpm
        ```

=== "Debian 10"
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
        systemctl restart php7.3-fpm
        ```

## SELinux

=== "Ubuntu 20.04"
    SELinux not enabled by default

=== "CentOS 8"
    Install the policy tool for SELinux:

    ```
    dnf install policycoreutils-python-utils
    ```

    ### Configure the contexts needed by LibreNMS

    ```
    semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/html(/.*)?'
    semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/(logs|rrd|storage)(/.*)?'
    restorecon -RFvv /opt/librenms
    setsebool -P httpd_can_sendmail=1
    setsebool -P httpd_execmem 1
    chcon -t httpd_sys_rw_content_t /opt/librenms/.env
    ```

    ### Allow fping

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

    Additional SELinux problems may be found by executing the following command

    ```
    audit2why < /var/log/audit/audit.log
    ```

=== "Debian 10"
    SELinux not enabled by default

## Allow access through firewall

=== "Ubuntu 20.04"
    Firewall not enabled by default

=== "CentOS 8"

    ```
    firewall-cmd --zone public --add-service http --add-service https
    firewall-cmd --permanent --zone public --add-service http --add-service https
    ```

=== "Debian 10"
    Firewall not enabled by default


## Enable lnms command completion

This feature grants you the opportunity to use tab for completion on lnms commands as you would
for normal linux commands.

```
ln -s /opt/librenms/lnms /usr/bin/lnms
cp /opt/librenms/misc/lnms-completion.bash /etc/bash_completion.d/
```

## Configure snmpd

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
cp /opt/librenms/librenms.nonroot.cron /etc/cron.d/librenms
```

> NOTE: Keep in mind  that cron, by default, only uses a very limited
> set of environment variables. You may need to configure proxy
> variables for the cron invocation. Alternatively adding the proxy
> settings in config.php is possible too. The config.php file will be
> created in the upcoming steps. Review the following URL after you
> finished librenms install steps:
> <@= config.site_url =@/Support/Configuration/#proxy-support>

## Copy logrotate config

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
<http://librenms.example.com/>.  Please note that we have not covered
 HTTPS setup in this example, so your LibreNMS install is not secure
 by default.  Please do not expose it to the public Internet unless
 you have configured HTTPS and taken appropriate web server hardening
 steps.

## Add the first device

We now suggest that you add localhost as your first device from within the WebUI.

## Troubleshooting

If you ever have issues with your install, run validate.php:

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
- [Alerting](../Extensions/Alerting.md)
- [Device Groups](../Extensions/Device-Groups.md)
- [Auto discovery](../Extensions/Auto-Discovery.md)

## Closing

We hope you enjoy using LibreNMS. If you do, it would be great if you
would consider opting into the stats system we have, please see [this
page](../General/Callback-Stats-and-Privacy.md) on
what it is and how to enable it.

If you would like to help make LibreNMS better there are [many ways to
help](../Support/FAQ.md#a-namefaq9-what-can-i-do-to-helpa). You
can also [back LibreNMS on Open Collective](https://t.libren.ms/donations).
