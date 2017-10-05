source: Extensions/RRDCached.md
# Setting up RRDCached

This document will explain how to setup RRDCached for LibreNMS.

> If you are using rrdtool / rrdcached version 1.5 or above then this now supports creating rrd files over rrdcached. 
If you have rrdcached 1.5.5 or above, we can also tune over rrdcached.
To enable this set the following config:

```php
$config['rrdtool_version'] = '1.5.5';
```

NOTE: This feature requires your client version of rrdtool to be 1.5.5 or over, in addition to your rrdcached version.

### Distributed Poller Support Matrix

Shared FS: Is a shared filesystem required?

Features: Supported features in the version indicated.

          G = Graphs.

          C = Create RRD files.

          U = Update RRD files.

          T = Tune RRD files.

| Version | Shared FS | Features |
| ------- | :-------: | -------- |
| 1.4.x   | Yes       | G,U      |
| <1.5.5  | Yes       | G,U      |
| >=1.5.5 | No        | G,C,U    |
| >=1.6.x | No        | G,C,U    |

### RRDCached installation Debian Jessie (rrdcached 1.4.8)
```ssh
sudo apt-get install rrdcached
```

- Edit /opt/librenms/config.php to include:
```php
$config['rrdcached']    = "unix:/var/run/rrdcached.sock";
```
- Edit /etc/default/rrdcached to include:
```ssh
OPTS="-s librenms"
OPTS="$OPTS -l unix:/var/run/rrdcached.sock"
OPTS="$OPTS -j /var/lib/rrdcached/journal/ -F"
OPTS="$OPTS -b /opt/librenms/rrd/ -B"
OPTS="$OPTS -w 1800 -z 1800 -f 3600 -t 4"
```

### RRDCached installation Ubuntu 16
```ssh
sudo apt-get install rrdcached
```

- Edit /opt/librenms/config.php to include:
```php
$config['rrdcached']    = "unix:/var/run/rrdcached.sock";
```
- Edit /etc/default/rrdcached to include:
```ssh
DAEMON=/usr/bin/rrdcached
DAEMON_USER=librenms
DAEMON_GROUP=librenms
WRITE_THREADS=4
WRITE_TIMEOUT=1800
WRITE_JITTER=1800
BASE_PATH=/opt/librenms/rrd/
JOURNAL_PATH=/var/lib/rrdcached/journal/
PIDFILE=/var/run/rrdcached.pid
SOCKFILE=/var/run/rrdcached.sock
SOCKGROUP=librenms
BASE_OPTIONS="-B -F -R"
```
- Set ownership and permissions.
```ssh
chmod 755 /var/run/rrdcached*
```

- Start RRDCached and enable RRDCached so the service will run at start up.
```ssh
systemctl start rrdcached
systemctl enable rrdcached
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
OPTIONS="-w 1800 -z 1800 -f 3600 -s librenms -U librenms -G librenms -B -R -j /var/tmp -l unix:/var/run/rrdcached/rrdcached.sock -t 4 -F -b /opt/librenms/rrd/"
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

- Create /etc/systemd/system/rrdcached.service with this content:
```ssh
[Unit]
Description=Data caching daemon for rrdtool
After=network.service

[Service]
Type=forking
PIDFile=/run/rrdcached.pid
ExecStart=/usr/bin/rrdcached -w 1800 -z 1800 -f 3600 -s librenms -U librenms -G librenms -B -R -j /var/tmp -l unix:/run/rrdcached.sock -t 4 -F -b /opt/librenms/rrd/

[Install]
WantedBy=default.target
```

- Start RRDCached
```ssh
systemctl enable --now rrdcached.service
```

- Edit /opt/librenms/config.php to include:
```ssh
$config['rrdcached'] = "unix:/run/rrdcached.sock";
```

Check to see if the graphs are being drawn in LibreNMS. This might take a few minutes.
After at least one poll cycle (5 mins), check the LibreNMS disk I/O performance delta.
Disk I/O can be found under the menu Devices>All Devices>[localhost hostname]>Health>Disk I/O.

Depending on many factors, you should see the Ops/sec drop by ~30-40%.

#### Securing RRCached
Please see [RRDCached Security](RRDCached-Security.md)

[1]: http://librenms.readthedocs.org/Installation/Installation-CentOS-7-Apache/
"Add localhost to LibreNMS"
