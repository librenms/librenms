source: Extensions/RRDCached.md
path: blob/master/doc/

# Setting up RRDCached

This document will explain how to setup RRDCached for LibreNMS.

Since version 1.5, rrdtool / rrdcached now supports creating rrd files
over rrdcached. If you have rrdcached 1.5.5 or above, you can also
tune over rrdcached. To enable this set the following config:

```php
$config['rrdtool_version'] = '1.5.5';
```

NOTE: This feature requires your client version of rrdtool to be 1.5.5
or over, in addition to your rrdcached version.

# Distributed Poller Support Matrix

Shared FS: Is a shared filesystem required?

Features: Supported features in the version indicated.

```
          G = Graphs.
          C = Create RRD files.
          U = Update RRD files.
          T = Tune RRD files.
```

| Version | Shared FS | Features |
| ------- | :-------: | -------- |
| 1.4.x   | Yes       | G,U      |
| <1.5.5  | Yes       | G,U      |
| >=1.5.5 | No        | G,C,U    |
| >=1.6.x | No        | G,C,U    |

It is recommended that you monitor your LibreNMS server with LibreNMS
so you can view the disk I/O usage delta.

## RRDCached installation CentOS 7

1: Create `/etc/systemd/system/rrdcached.service` with this content:

```
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

2: Start rrdcached

```bash
systemctl enable --now rrdcached.service
```

3: Edit `/opt/librenms/config.php` to include:

```php
$config['rrdcached'] = "unix:/run/rrdcached.sock";
```

## RRDCached installation Ubuntu 16

1: Install rrdcached

```bash
sudo apt-get install rrdcached
```

2: Edit `/etc/default/rrdcached` to include:

```
DAEMON=/usr/bin/rrdcached
DAEMON_USER=librenms
DAEMON_GROUP=librenms
WRITE_THREADS=4
WRITE_TIMEOUT=1800
WRITE_JITTER=1800
BASE_PATH=/opt/librenms/rrd/
JOURNAL_PATH=/var/lib/rrdcached/journal/
PIDFILE=/run/rrdcached.pid
SOCKFILE=/run/rrdcached.sock
SOCKGROUP=librenms
BASE_OPTIONS="-B -F -R"
```

2: Fix permissions

```bash
chown librenms:librenms /var/lib/rrdcached/journal/
```

3: Restart the rrdcached service

```bash
systemctl restart rrdcached.service
```

5: Edit `/opt/librenms/config.php` to include:

```php
$config['rrdcached'] = "unix:/var/run/rrdcached.sock";
```

## RRDCached installation Debian Jessie (rrdcached 1.4.8)

1: Install rrdcached

```bash
sudo apt-get install rrdcached
```

2: Edit /etc/default/rrdcached to include:

```bash
OPTS="-s librenms"
OPTS="$OPTS -l unix:/var/run/rrdcached.sock"
OPTS="$OPTS -j /var/lib/rrdcached/journal/ -F"
OPTS="$OPTS -b /opt/librenms/rrd/ -B"
OPTS="$OPTS -w 1800 -z 1800 -f 3600 -t 4"
```

3: Restart the rrdcached service

```bash
    systemctl restart rrdcached.service
```

4: Edit /opt/librenms/config.php to include:

```php
$config['rrdcached'] = "unix:/var/run/rrdcached.sock";
```

## RRDCached installation Debian Stretch (rrdcached 1.6.0)

1: Install rrdcached

```bash
sudo apt-get install rrdcached
```

2; Edit /etc/default/rrdcached to include:

```bash
DAEMON=/usr/bin/rrdcached
WRITE_TIMEOUT=1800
WRITE_JITTER=1800
WRITE_THREADS=4
BASE_PATH=/opt/librenms/rrd/
JOURNAL_PATH=/var/lib/rrdcached/journal/
PIDFILE=/var/run/rrdcached.pid
SOCKFILE=/var/run/rrdcached.sock
SOCKGROUP=librenms
DAEMON_GROUP=librenms
DAEMON_USER=librenms
BASE_OPTIONS="-B -F -R"
```

3: Fix permissions

```bash
chown librenms:librenms /var/lib/rrdcached/journal/
```

4: Restart the rrdcached service

```bash
    systemctl restart rrdcached.service
```

5: Edit /opt/librenms/config.php to include:

For local RRDCached server

```php
$config['rrdcached'] = "unix:/var/run/rrdcached.sock";
```

For remote RRDCached server make sure you have network option in /var/default/rrdcached

```bash
NETWORK_OPTIONS="-L"
```

```php
$config['rrdcached'] = "IPADDRESS:42217";
```

NOTE: change IPADDRESS to the ip the rrdcached server is listening on.

## RRDCached installation CentOS 6

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

# Verify

Check to see if the graphs are being drawn in LibreNMS. This might take a few minutes.
After at least one poll cycle (5 mins), check the LibreNMS disk I/O performance delta.
Disk I/O can be found under the menu Devices>All Devices>[localhost
hostname]>Health>Disk I/O.

Depending on many factors, you should see the Ops/sec drop by ~30-40%.

# Securing RRCached

Please see [RRDCached Security](RRDCached-Security.md)

[1]: http://librenms.readthedocs.org/Installation/Installation-CentOS-7-Apache/
"Add localhost to LibreNMS"
