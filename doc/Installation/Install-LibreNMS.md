# Install LibreNMS

## Prepare Linux Server

These instructions need an installed Linux server with a supported operating system.
In the tabs below, select the OS of your server.
You can choose the web server. We recommend NGINX.

Connect to the command line of the server. Then obey the instructions below.
!!! note

    These instructions assume that you are the **root** user.  
    A user that is not root must add `sudo` before each shell command. The
    commands at a `mysql>` prompt do not need `sudo`. Such a user can also
    become a user with root privileges with `sudo -s` or `sudo -i`.

**The minimum supported PHP version is @= php.version_min =@, the recommended version is @= php.version_recommended =@.**

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

=== "RHEL Derivatives"
    === "Version 8.X"
        Tested with Rocky Linux 8.10, AlmaLinux 8.10, Oracle Enterprise Linux 8.10
        ```
        dnf -y install epel-release
        dnf -y install dnf-utils http://rpms.remirepo.net/enterprise/remi-release-8.rpm
        dnf module reset php
        dnf -y module enable php:remi-8.5
        dnf -y install bash-completion cronie fping gcc git mariadb-server mtr net-snmp net-snmp-utils nmap php-fpm php-cli php-common php-curl php-gd php-gmp php-json php-mbstring php-process php-snmp php-xml php-zip php-mysqlnd policycoreutils-python-utils python3 python3-devel python3-PyMySQL python3-redis python3-memcached python3-pip python3-systemd rrdtool unzip
        ```
        === "NGINX"
            ```
            dnf -y install nginx
            ```

        === "Apache"
            ```
            dnf -y install httpd
            ```
    === "Version 9.X"
        Tested with Rocky Linux 9.8, AlmaLinux 9.8, Oracle Enterprise Linux 9.8
        ```
        dnf -y install epel-release
        dnf -y install dnf-utils http://rpms.remirepo.net/enterprise/remi-release-9.rpm
        dnf module reset php
        dnf -y module enable php:remi-8.5
        dnf -y install bash-completion cronie fping gcc git mariadb-server mtr net-snmp net-snmp-utils nmap php-fpm php-cli php-common php-curl php-gd php-gmp php-json php-mbstring php-process php-snmp php-xml php-zip php-mysqlnd policycoreutils-python-utils python3 python3-devel python3-PyMySQL python3-redis python3-memcached python3-pip python3-systemd rrdtool unzip
        ```
        === "NGINX"
            ```
            dnf -y install nginx
            ```

        === "Apache"
            ```
            dnf -y install httpd
            ```

    === "Version 10.X"
        Tested with Rocky Linux 10.2, AlmaLinux 10.2, Oracle Enterprise Linux 10.2
        === "Rocky or Alma"
            ```
            dnf -y install epel-release
            dnf -y install http://rpms.remirepo.net/enterprise/remi-release-10.rpm
            dnf module reset php
            dnf -y module enable php:remi-8.5
            dnf -y install acl bash-completion cronie fping gcc git mariadb-server mtr net-snmp net-snmp-utils nmap php-fpm php-cli php-common php-curl php-gd php-gmp php-json php-mbstring php-process php-snmp php-xml php-zip php-mysqlnd policycoreutils-python-utils python3 python3-devel python3-PyMySQL python3-redis python3-pip python3-setuptools python3-systemd rrdtool unzip
            ```
            === "NGINX"
                ```
                dnf -y install nginx
                ```

            === "Apache"
                ```
                dnf -y install httpd
                ```
        === "Oracle"
            ```
            dnf -y install oracle-epel-release-el10
            # oracle-epel-release-el10 does not satisfy the "epel-release" dependency but has the required packages
            rpm -i --nodeps http://rpms.remirepo.net/enterprise/remi-release-10.rpm
            dnf module reset php
            dnf module enable php:remi-8.5
            dnf -y install acl bash-completion cronie fping gcc git mariadb-server mtr net-snmp net-snmp-utils nmap php-fpm php-cli php-common php-curl php-gd php-gmp php-json php-mbstring php-process php-snmp php-xml php-zip php-mysqlnd policycoreutils-python-utils python3 python3-devel python3-PyMySQL python3-redis python3-pip python3-setuptools python3-systemd rrdtool unzip
            ```
            === "NGINX"
                ```
                dnf -y install nginx
                ```

            === "Apache"
                ```
                dnf -y install httpd
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

Then run the composer wrapper script and go back to the root user:
```
./scripts/composer_wrapper.php install --no-dev
exit
```

!!! note
    If a proxy supplies the internet access, this script can fail.
    The correction is a manual installation of the `composer` package. For a global installation:
    ```
    wget https://getcomposer.org/composer-stable.phar
    mv composer-stable.phar /usr/bin/composer
    chmod +x /usr/bin/composer
    ```

## Set timezone

For a list of the supported timezones, see
<https://php.net/manual/en/timezones.php>. Examples are `America/New_York`,
`Australia/Brisbane`, and `Etc/UTC`. In `php.ini`, set `date.timezone` to
your timezone.

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

=== "RHEL Derivatives"
    ```
    vi /etc/php.ini
    ```

Also set the system timezone.

```
timedatectl set-timezone Etc/UTC
```


## Configure MariaDB

=== "Ubuntu 26.04"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```
    
    In the `[mariadbd]` section, add:

    ```
    innodb_file_per_table=1
    lower_case_table_names=0
    ```

=== "Ubuntu 24.04"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```

    In the `[mysqld]` section, add:

    ```
    innodb_file_per_table=1
    lower_case_table_names=0
    ```

=== "Debian 12"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```

    In the `[mysqld]` section, add:

    ```
    innodb_file_per_table=1
    lower_case_table_names=0
    ```    

=== "Debian 13"
    ```
    vi /etc/mysql/mariadb.conf.d/50-server.cnf
    ```

    In the `[mariadbd]` section, add:

    ```
    innodb_file_per_table=1
    lower_case_table_names=0
    ```

=== "RHEL Derivatives"
    ```
    vi /etc/my.cnf.d/mariadb-server.cnf
    ```

    Within the `[mariadb]` section add:

    ```
    innodb_file_per_table=1
    lower_case_table_names=0
    ```

Then restart MariaDB:

```
systemctl enable mariadb
systemctl restart mariadb
```
Start the MariaDB client:

```
mysql -u root
```

!!! warning
    Change `password` in the commands below to a secure password.

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

=== "RHEL Derivatives"
    ```bash
    cp /etc/php-fpm.d/www.conf /etc/php-fpm.d/librenms.conf
    vi /etc/php-fpm.d/librenms.conf
    ```

Change `[www]` to `[librenms]`:
```
[librenms]
```

Change `user` and `group` to `librenms`:
```
user = librenms
group = librenms
```

Change `listen` to a unique path. This path must match the configuration of your
web server. The relevant key is `fastcgi_pass` for NGINX and `SetHandler` for Apache:
```
listen = /run/php-fpm-librenms.sock
```

If this server has no other PHP web application, you can remove `www.conf`.
This saves resources. You can also tune the performance settings in `librenms.conf`.

## Configure Web Server

=== "Ubuntu 26.04"
    === "NGINX"
        ```bash
        vi /etc/nginx/conf.d/librenms.conf
        ```

        Add this configuration. Change `server_name` for your server:

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

        Add this configuration. Change `server_name` for your server:

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

        Add this configuration. Change `server_name` for your server:

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

        Add this configuration. Change `server_name` for your server:

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

=== "RHEL Derivatives"
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

## SELinux

=== "Ubuntu 26.04"
    SELinux is not enabled by default.

=== "Ubuntu 24.04"
    SELinux is not enabled by default.

=== "Debian 12"
    SELinux is not enabled by default.

=== "Debian 13"
    SELinux is not enabled by default.

=== "RHEL Derivatives"

    <h3>Configure the contexts needed by LibreNMS</h3>

    ```
    semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/html(/.*)?'
    semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/(rrd|storage)(/.*)?'
    semanage fcontext -a -t httpd_log_t "/opt/librenms/logs(/.*)?"
    semanage fcontext -a -t httpd_cache_t '/opt/librenms/cache(/.*)?'
    semanage fcontext -a -t bin_t '/opt/librenms/librenms-service.py'
    restorecon -RFvv /opt/librenms
    setsebool -P httpd_can_sendmail=1
    setsebool -P httpd_execmem 1
    setsebool -P httpd_can_network_connect_db 1
    chcon -t httpd_sys_rw_content_t /opt/librenms/.env
    ```

    <h3>Allow fping</h3>

    Create the file http_fping.tt with the following contents. You can
    create this file anywhere, as it is a throw-away file. The last step
    in this install procedure will install the module in the proper
    location.

    ```
    module http_fping 1.0;

    require {
    type node_t;
    type httpd_t;
    class capability net_raw;
    class icmp_socket create;
    class rawip_socket { getopt create setopt write read bind node_bind };
    }

    #============= httpd_t ==============
    allow httpd_t node_t:rawip_socket node_bind;
    allow httpd_t self:capability net_raw;
    allow httpd_t self:icmp_socket create;
    allow httpd_t self:rawip_socket { getopt create setopt write read bind };
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

## Allow access through firewall
=== "Ubuntu 26.04"
    The firewall is not enabled by default.

=== "Ubuntu 24.04"
    The firewall is not enabled by default.

=== "Debian 12"
    The firewall is not enabled by default.

=== "Debian 13"
    The firewall is not enabled by default.

=== "RHEL Derivatives"
    ```
    firewall-cmd --zone public --add-service http --add-service https
    firewall-cmd --permanent --zone public --add-service http --add-service https
    ```

## Enable lnms command completion

This feature lets you use the tab key to complete `lnms` commands. It works in the
same way as for normal Linux commands.

```
ln -s /opt/librenms/lnms /usr/bin/lnms
cp /opt/librenms/misc/lnms-completion.bash /etc/bash_completion.d/
```

`lnms config` is the preferred method for [configuration](../Support/Configuration.md).


## Configure snmpd (v2c)

To use SNMPv3, see the [SNMP configuration examples](../Support/SNMP-Configuration-Examples.md/#linux-snmpd-v3).

```
cp /opt/librenms/snmpd.conf.example /etc/snmp/snmpd.conf
```

```
vi /etc/snmp/snmpd.conf
```

Replace the text `RANDOMSTRINGGOESHERE` with your own community string.

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
    By default, cron uses only a small set of environment variables.
    The cron job can therefore need proxy variables. You can also put
    the proxy settings in `config.php`. A later step creates the
    `config.php` file. After the install, read this page:
    <@= config.site_url =@/Support/Configuration/#proxy-support>

## Enable the scheduler

```
cp /opt/librenms/dist/librenms-scheduler.service /opt/librenms/dist/librenms-scheduler.timer /etc/systemd/system/

systemctl enable librenms-scheduler.timer
systemctl start librenms-scheduler.timer
```

## Enable logrotate

LibreNMS keeps logs in `/opt/librenms/logs`. These logs become large
with time. To rotate the old logs, use the supplied logrotate
configuration file:

```
cp /opt/librenms/misc/librenms.logrotate /etc/logrotate.d/librenms
```

## Web installer

Open the web installer. Then obey the instructions on the screen.

<http://librenms.example.com/install>

The web installer can ask you to create a `config.php` file in your
LibreNMS install location. Copy the content from the screen into the
file. Then set the permissions on `config.php` with this command:

```
chown librenms:librenms /opt/librenms/config.php
```

## Final steps

The install is complete. You can now log in at
<http://librenms.example.com/>.

!!! danger
    Do not connect this install to the public internet before you
    configure HTTPS. This procedure does not configure HTTPS, and the
    install is not secure. Also harden the web server.

## Add the first device

Add `localhost` as your first device in the web interface.
<https://librenms.example.com/addhost>

## Troubleshooting

If your install has a problem, run the validation script. The script does
basic tests and gives the recommended corrections:

```
sudo su - librenms
./validate.php
```

The LibreNMS web site lists more sources of help:
<https://www.librenms.org/#support>

## What next?

After the install, read these documents:

- [Performance tuning](../Support/Performance.md)
- [Alerting](../Alerting/index.md)
- [Device Groups](../Extensions/Device-Groups.md)
- [Auto discovery](../Extensions/Auto-Discovery.md)
- [High Availability](../Support/High-Availability.md)

## Closing

Please consider an opt-in to our statistics system. For a description of
the system and the steps to enable it, read [this
page](../General/Callback-Stats-and-Privacy.md).

There are [many ways to help](../Support/FAQ.md#faq9) to make LibreNMS
better. You can also [back LibreNMS on Open
Collective](https://t.libren.ms/donations).
