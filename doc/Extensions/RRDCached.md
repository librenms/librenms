# Setting up RRDCached

This document will explain how to setup RRDCached for LibreNMS.

### RRDCached installation
This example is based on a fresh LibreNMS install, on a minimimal CentOS installation.
In this example, we'll use the Repoforge repository.

```php
rpm -ivh http://pkgs.repoforge.org/rpmforge-release/rpmforge-release-0.5.3-1.el6.rf.x86_64.rpm
vi /etc/yum.repos.d/rpmforge.repo
```
- Enable the Extra repo

```php
yum update rrdtool
vi /etc/yum.repos.d/rpmforge.repo 
```
- Disable the [rpmforge] and [rpmforge-extras] repos again

```php
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

Edit config.php to include:
```php
$config['rrdcached']    = "unix:/var/run/rrdcached/rrdcached.sock";
```

> If you are using rrdtool / rrdcached version 1.5 or above then this now supports creating rrd files over rrdcached. To 
enable this set the following config:

```php
$config['rrdtool_version'] = 1.5;
```
