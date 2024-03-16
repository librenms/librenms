# Setting up RRDCached

This document will explain how to set up RRDCached for LibreNMS.

Since version 1.5, rrdtool / rrdcached now supports creating rrd files
over rrdcached. If you have rrdcached 1.5.5 or above, you can also
tune over rrdcached. To enable this set the following config:

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdtool_version '1.5.5'
    ```

This setting has to be the exact version of rrdtool you are running.

NOTE: This feature requires your client version of rrdtool to be 1.5.5
or newer, in addition to your rrdcached version.

## Distributed Poller Support Matrix

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


## Installation Manual for

1. [RRDCached installation Ubuntu 16](#rrdcached-installation-ubuntu-16)
1. [RRDCached installation Debian Buster](#rrdcached-installation-debian-buster)
1. [RRDCached installation Debian Stretch](#rrdcached-installation-debian-stretch)
1. [RRDCached installation CentOS 7 or 8](#rrdcached-installation-centos-7-or-8)
1. [RRDCached installation CentOS 6](#rrdcached-installation-centos-6)
1. [Securing RRCached](#securing-rrcached)


### RRDCached installation Ubuntu 16

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

5: Edit your config to include:

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "unix:/run/rrdcached.sock"
    ```

### RRDCached installation Debian Buster
(rrdcached 1.7.1)

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
SOCKFILE=/run/rrdcached.sock
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

5: Edit your config to include:

For local RRDCached server

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "unix:/run/rrdcached.sock"
    ```

For remote RRDCached server make sure you have network option in /var/default/rrdcached

```bash
NETWORK_OPTIONS="-L"
```

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "IPADDRESS:42217"
    ```

NOTE: change IPADDRESS to the ip the rrdcached server is listening on.

### RRDCached installation Debian Stretch
(rrdcached 1.6.0)

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
SOCKFILE=/run/rrdcached.sock
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

5: Edit your config to include:

For local RRDCached server

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "unix:/run/rrdcached.sock"
    ```

For remote RRDCached server make sure you have network option in /var/default/rrdcached

```bash
NETWORK_OPTIONS="-L"
```

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "IPADDRESS:42217"
    ```

NOTE: change IPADDRESS to the ip the rrdcached server is listening on.

### RRDCached installation CentOS 7 or 8

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

2: Configure SELinux for RRDCached

```
cat > rrdcached_librenms.te << EOF
module rrdcached_librenms 1.0;
 
require {
        type var_run_t;
        type tmp_t;
        type httpd_t;
        type rrdcached_t;
        type httpd_sys_rw_content_t;
        class dir { add_name getattr open read remove_name rmdir search write };
        class file { create getattr open read rename setattr unlink write map lock };
        class sock_file { create setattr unlink write };
        class capability { fsetid sys_resource };
        class unix_stream_socket connectto;
}
 
#============= rrdcached_t ==============
 
allow rrdcached_t httpd_sys_rw_content_t:dir { add_name getattr remove_name search write };
allow rrdcached_t httpd_sys_rw_content_t:file { create getattr open read rename setattr unlink write map lock };
allow rrdcached_t self:capability fsetid;
allow rrdcached_t var_run_t:sock_file { create setattr unlink };
allow httpd_t var_run_t:sock_file write;
allow httpd_t rrdcached_t:unix_stream_socket connectto;
EOF

checkmodule -M -m -o rrdcached_librenms.mod rrdcached_librenms.te
semodule_package -o rrdcached_librenms.pp -m rrdcached_librenms.mod
semodule -i rrdcached_librenms.pp
```

3: Start rrdcached

```bash
systemctl enable --now rrdcached.service
```

4: Edit your config to include:

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "unix:/run/rrdcached.sock"
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
OPTIONS="-w 1800 -z 1800 -f 3600 -s librenms -U librenms -G librenms -B -R -j /var/tmp -l unix:/run/rrdcached.sock -t 4 -F -b /opt/librenms/rrd/"
RRDC_USER=librenms

mkdir /var/run/rrdcached
chown librenms:librenms /var/run/rrdcached/
chown librenms:librenms /var/rrdtool/
chown librenms:librenms /var/rrdtool/rrdcached/
chkconfig rrdcached on
service rrdcached start
```

- Edit your config to include:

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "unix:/run/rrdcached.sock"
    ```

## Verify

Check to see if the graphs are being drawn in LibreNMS. This might take a few minutes.
After at least one poll cycle (5 mins), check the LibreNMS disk I/O performance delta.
Disk I/O can be found under the menu Devices>All Devices>[localhost
hostname]>Health>Disk I/O.

Depending on many factors, you should see the Ops/sec drop by ~30-40%.

## Securing RRCached

According to the [man page](https://linux.die.net/man/1/rrdcached),
under "SECURITY CONSIDERATIONS", rrdcached has no authentication or
security except for running under a unix socket. If you choose to use
a network socket instead of a unix socket, you will need to secure
your rrdcached installation. To do so you can proxy rrdcached using
nginx to allow only specific IPs to connect.

Using the same setup above, using nginx version 1.9.0 or later, you
can follow this setup to proxy the default rrdcached port to the local
unix socket.

(You can use `./conf.d` for your configuration as well)

`mkdir /etc/nginx/streams-{available,enabled}`

add the following to your nginx.conf file:

```nginx
#/etc/nginx/nginx.conf
...
stream {
    include /etc/nginx/streams-enabled/*;
}
```

Add this to `/etc/nginx/streams-available/rrd`

```nginx
server {
    listen 42217;

    error_log  /var/log/nginx/rrd.stream.error.log;

    allow $LibreNMS_IP;
    deny all;

    proxy_pass unix:/run/rrdcached.sock;
}

```

Replace `$LibreNMS_IP` with the ip of the server that will be using
rrdcached. You can specify more than one `allow` statement. This will
bind nginx to TCP 42217 (the default rrdcached port), allow the
specified IPs to connect, and deny all others.

next, we'll symlink the config to streams-enabled:
`ln -s /etc/nginx/streams-{available,enabled}/rrd`

and reload nginx
`service nginx reload`
