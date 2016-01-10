# Setting up RRDCached

This document will explain how to setup RRDCached for LibreNMS.

> If you are using rrdtool / rrdcached version 1.5 or above then this now supports creating rrd files over rrdcached. To 
enable this set the following config:

```php
$config['rrdtool_version'] = 1.5;
```

### RRDCached installation CentOS 6
This example is based on a fresh LibreNMS install, on a minimal CentOS 6 installation.
In this example, we'll use the Repoforge repository.

```ssh
rpm -ivh http://pkgs.repoforge.org/rpmforge-release/rpmforge-release-0.5.3-1.el6.rf.x86_64.rpm
vi /etc/yum.repos.d/rpmforge.repo
```
- Enable the Extra repo

```ssh
yum update rrdtool
vi /etc/yum.repos.d/rpmforge.repo 
```
- Disable the [rpmforge] and [rpmforge-extras] repos again

```ssh
vi /etc/sysconfig/rrdcached

# Settings for rrdcached
OPTIONS="-w 1800 -z 1800 -f 3600 -s librenms -j /var/tmp -l unix:/var/run/rrdcached/rrdcached.sock -t 4 -F -b /opt/librenms/rrd/"
RRDC_USER=librenms

mkdir /var/run/rrdcached
chown librenms:librenms /var/run/rrdcached/
chown librenms:librenms /var/rrdtool/
chown librenms:librenms /var/rrdtool/rrdcached/
chkconfig rrdcached on
service rrdcached start
```

- Edit /opt/librenms/config.php to include:
```php
$config['rrdcached']    = "unix:/var/run/rrdcached/rrdcached.sock";
```
### RRDCached installation CentOS 7
This example is based on a fresh LibreNMS install, on a minimimal CentOS 7.x installation.
We'll use the epel-release and setup a RRDCached as a service.
It is recommended that you monitor your LibreNMS server with LibreNMS so you can view the disk I/O usage delta.
See [Installation (RHEL CentOS)][1] for localhost monitoring.

- Install the EPEL and update the repos and RRDtool.
```ssh
yum install epel-release
yum update
yum update rrdtool
```

- Create the needed directories, set ownership and permissions.
```ssh
mkdir /var/run/rrdcached
chown librenms:librenms /var/run/rrdcached
chmod 755 /var/run/rrdcached
```

- Create an rrdcached service for easy daemon management.
```ssh
touch /etc/systemd/system/rrdcached.service
```
- Edit rrdcached.service and paste the example config:
```ssh
[Unit]
Description=RRDtool Cache
After=network.service

[Service]
Type=forking
PIDFile=/run/rrdcached.pid
ExecStart=/usr/bin/rrdcached -w 1800 -z 1800 -f 3600 -s librenms -j /var/tmp -l unix:/var/run/rrdcached/rrdcached.sock -t 4 -F -b /opt/librenms/rrd/
RRDC_USER=librenms

[Install]
WantedBy=default.target
```

- Restart the systemctl daemon so it can recognize the newly created rrdcached.service. Enable the rrdcached.service on boot, and start the service.
```ssh
systemctl daemon-reload
systemctl enable rrdcached.service
systemctl start rrdcached.service
```

- Edit /opt/librenms/config.php to include:
```ssh
$config['rrdcached']    = "unix:/var/run/rrdcached/rrdcached.sock";
```

- Restart Apache
```ssh
systemctl restart httpd
```

Check to see if the graphs are being drawn in LibreNMS. This might take a few minutes.
After at least one poll cycle (5 mins), check the LibreNMS disk I/O performance delta.
Disk I/O can be found under the menu Devices>All Devices>[localhost hostname]>Health>Disk I/O.

Depending on many factors, you should see the Ops/sec drop by ~30-40%.


[1]: http://librenms.readthedocs.org/Installation/Installation-(RHEL-CentOS)/#add-localhost
"Add localhost to LibreNMS"
