# Setting up RRDCached

This document will explain how to setup RRDCached for LibreNMS.

### RRDCached installation
This example is based on a fresh LibreNMS install, on a minimimal CentOS installation.
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
service rrdcached start
chkconfig rrdcached on
vi /etc/sysconfig/rrdcached

# Settings for rrdcached
OPTIONS="-w 1800 -z 1800 -f 3600 -s librenms -j /var/tmp -l unix:/var/run/rrdcached/rrdcached.sock -t 4 -F -b /opt/librenms/rrd/"
RRDC_USER=librenms

mkdir /var/run/rrdcached
chown librenms:librenms /var/run/rrdcached/
chown librenms:librenms /var/rrdtool/
chown librenms:librenms /var/rrdtool/rrdcached/
service rrdcached restart
```

Edit config.php to include:
```ssh
$config['rrdcached']    = "unix:/var/run/rrdcached/rrdcached.sock";
```
