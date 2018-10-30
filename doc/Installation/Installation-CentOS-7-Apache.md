source: Installation/Installation-CentOS-7-Apache.md
path: blob/master/doc/
> NOTE: These instructions assume you are the **root** user.  If you are not, prepend `sudo` to the shell commands (the ones that aren't at `mysql>` prompts) or temporarily become a user with root privileges with `sudo -s` or `sudo -i`.

**Please note the minimum supported PHP version is 5.6.4**

## Install Required Packages ##

    yum install epel-release

    rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm

    yum install composer cronie fping git httpd ImageMagick jwhois mariadb mariadb-server mtr MySQL-python net-snmp net-snmp-utils nmap php72w php72w-cli php72w-common php72w-curl php72w-gd php72w-mbstring php72w-mysqlnd php72w-process php72w-snmp php72w-xml php72w-zip python-memcached rrdtool

#### Add librenms user

    useradd librenms -d /opt/librenms -M -r
    usermod -a -G librenms apache

#### Install LibreNMS

    cd /opt
    composer create-project --no-dev --keep-vcs librenms/librenms librenms dev-master

## DB Server ##

#### Configure MySQL

    systemctl start mariadb
    mysql -u root

> NOTE: Please change the 'password' below to something secure.
```sql
CREATE DATABASE librenms CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
FLUSH PRIVILEGES;
exit
```

    vi /etc/my.cnf

Within the `[mysqld]` section please add:

```bash
innodb_file_per_table=1
lower_case_table_names=0
```
    systemctl enable mariadb
    systemctl restart mariadb

## Web Server ##

### Configure PHP

Ensure date.timezone is set in php.ini to your preferred time zone.  See http://php.net/manual/en/timezones.php for a list of supported timezones.  Valid examples are: "America/New_York", "Australia/Brisbane", "Etc/UTC".

    vi  /etc/php.ini

### Configure Apache

    vi /etc/httpd/conf.d/librenms.conf

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

> NOTE: If this is the only site you are hosting on this server (it should be :)) then you will need to disable the default site.
`rm -f /etc/httpd/conf.d/welcome.conf`

    systemctl enable httpd
    systemctl restart httpd

#### SELinux

Install the policy tool for SELinux:

    yum install policycoreutils-python

##### Configure the contexts needed by LibreNMS:

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
    setsebool -P httpd_can_sendmail=1

##### Allow fping
Create the file http_fping.tt with the following contents. You can create this file anywhere, as it is a throw-away file. The last step in this install procedure will install the module in the proper location.
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

    checkmodule -M -m -o http_fping.mod http_fping.tt
    semodule_package -o http_fping.pp -m http_fping.mod
    semodule -i http_fping.pp

#### Allow access through firewall

    firewall-cmd --zone public --add-service http
    firewall-cmd --permanent --zone public --add-service http
    firewall-cmd --zone public --add-service https
    firewall-cmd --permanent --zone public --add-service https

### Configure snmpd

  
Copy the example snmpd.conf from the LibreNMS install.

    cp /opt/librenms/snmpd.conf.example /etc/snmp/snmpd.conf

    vi /etc/snmp/snmpd.conf

Edit the text which says `RANDOMSTRINGGOESHERE` and set your own community string.

    curl -o /usr/bin/distro https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/distro
    chmod +x /usr/bin/distro
    systemctl enable snmpd
    systemctl restart snmpd

### Cron job

    cp /opt/librenms/librenms.nonroot.cron /etc/cron.d/librenms

#### Copy logrotate config

LibreNMS keeps logs in `/opt/librenms/logs`. Over time these can become large and be rotated out.  To rotate out the old logs you can use the provided logrotate config file:

    cp /opt/librenms/misc/librenms.logrotate /etc/logrotate.d/librenms

### Set permissions

    chown -R librenms:librenms /opt/librenms
    setfacl -d -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/
    setfacl -R -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/

## Web installer ##

Now head to the web installer and follow the on-screen instructions.

    http://librenms.example.com/install.php

The web installer might prompt you to create a `config.php` file in your librenms install location manually, copying the content displayed on-screen to the file. If you have to do this, please remember to set the permissions on config.php after you copied the on-screen contents to the file. Run:

    chown librenms:librenms /opt/librenms/config.php


### Final steps

That's it!  You now should be able to log in to http://librenms.example.com/.  Please note that we have not covered HTTPS setup in this example, so your LibreNMS install is not secure by default.  Please do not expose it to the public Internet unless you have configured HTTPS and taken appropriate web server hardening steps.

#### Add the first device

We now suggest that you add localhost as your first device from within the WebUI.

#### Troubleshooting

If you ever have issues with your install, run validate.php as root in the librenms directory:

    cd /opt/librenms
    ./validate.php

There are various options for getting help listed on the LibreNMS web site: https://www.librenms.org/#support

### What next?

Now that you've installed LibreNMS, we'd suggest that you have a read of a few other docs to get you going:

 - [Performance tuning](http://docs.librenms.org/Support/Performance)
 - [Alerting](http://docs.librenms.org/Extensions/Alerting/)
 - [Device Groups](http://docs.librenms.org/Extensions/Device-Groups/)
 - [Auto discovery](http://docs.librenms.org/Extensions/Auto-Discovery/)

### Closing

We hope you enjoy using LibreNMS. If you do, it would be great if you would consider opting into the stats system we have, please see [this page](http://docs.librenms.org/General/Callback-Stats-and-Privacy/) on what it is and how to enable it.

If you would like to help make LibreNMS better there are [many ways to help](http://docs.librenms.org/Support/FAQ/#what-can-i-do-to-help). You can also [back LibreNMS on Open Collective](https://t.libren.ms/donations).
