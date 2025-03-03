# Setting up RRDCached

 - [Github: Oetiker RRDCached](https://github.com/oetiker/rrdtool-1.x/)
 - [RRDCached](https://oss.oetiker.ch/rrdtool/doc/rrdcached.en.html)

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
| >=1.8.x | No        | G,C,U,T  |

It is recommended that you monitor your LibreNMS server with LibreNMS
so you can view the disk I/O usage delta.


## Installation

Ubuntu and Debian are very similar, the main difference is the location of the `PIDFILE`.

=== "Ubuntu"

    For info about version of rrdcached to install, see [Ubuntu packages](https://launchpad.net/ubuntu/+source/rrdtool/)

     1. Install rrdcached

        ```bash
        sudo apt-get install rrdcached
        ```

     2. Edit `/etc/default/rrdcached` to include:

        ```bash
        BASE_OPTIONS="-B -F -R"
        BASE_PATH=/opt/librenms/rrd/
        DAEMON_GROUP=librenms
        DAEMON_USER=librenms
        DAEMON=/usr/bin/rrdcached
        JOURNAL_PATH=/var/lib/rrdcached/journal/
        PIDFILE=/run/rrdcached.pid
        SOCKFILE=/run/rrdcached.sock
        SOCKGROUP=librenms
        WRITE_JITTER=1800
        WRITE_THREADS=4
        WRITE_TIMEOUT=1800
        ```

     3. Fix permissions

        ```bash
        chown librenms:librenms /var/lib/rrdcached/journal/
        ```

     4. Restart the rrdcached service

        ```bash
        systemctl restart rrdcached.service
        ```

=== "Debian"
    
    For info about version of rrdcached to install, see [Debian packages](https://packages.debian.org/search?keywords=rrdcached)


     1. Install rrdcached

        ```bash
        sudo apt-get install rrdcached
        ```

     2. Edit `/etc/default/rrdcached` to include:

        ```bash
        BASE_OPTIONS="-B -F -R"
        BASE_PATH=/opt/librenms/rrd/
        DAEMON_GROUP=librenms
        DAEMON_USER=librenms
        DAEMON=/usr/bin/rrdcached
        JOURNAL_PATH=/var/lib/rrdcached/journal/
        PIDFILE=/var/run/rrdcached.pid
        SOCKFILE=/run/rrdcached.sock
        SOCKGROUP=librenms
        WRITE_JITTER=1800
        WRITE_THREADS=4
        WRITE_TIMEOUT=1800
        ```

     3. Fix permissions

        ```bash
        chown librenms:librenms /var/lib/rrdcached/journal/
        ```

     4. Restart the rrdcached service

        ```bash
        systemctl restart rrdcached.service
        ```


=== "Red Hat/CentOS 8"

    !!! note
        `rrdcached` is installed as part of the `rrdtool` package, but the `rrdcached` service is not setup by default, unlike the Ubuntu/Debian setup.

        The intermediate files generated during the process for the SELinux policy (e.g., `rrdcached_librenms.mod` and `rrdcached_librenms.pp`) do not need to be saved after the policy module is successfully installed.


     1. link in the service and reload:

        ```bash
        ln -s /opt/librenms/dist/rrdcached/rrdcached.service /etc/systemd/system/
        systemctl daemon-reload
        ```

     2. Configure SELinux for RRDCached


         1. Compile the SELinux Policy Compile the SELinux policy module using the following command:

            ```bash
            checkmodule -M -m -o /tmp/rrdcached_librenms.mod /opt/librenms/dist/rrdcached/rrdcached_librenms.te
            ```

            Explanation:
             - `-M`: Enable the module compiler.
             - `-m`: Enable the module version format.
             - `-o`: Specify the output file name.

         2. Package the Policy Module Package the compiled module into a loadable policy package:

            ```bash
            semodule_package -o /tmp/rrdcached_librenms.pp -m /tmp/rrdcached_librenms.mod
            ```

            Explanation:
             - `-o`: Specify the output file name.
             - `-m`: Specify the input file name.

         3. Apply the Policy Module Apply the policy module to the system:

            ```bash
            semodule -i /tmp/rrdcached_librenms.pp
            ```

            Explanation:
             - `-i`: Install the policy module.

     3. Start RRDcached and enable for start at boot

        ```bash
        systemctl enable --now rrdcached.service
        ```

=== "CentOS 6"

    This example is based on a fresh LibreNMS install, on a minimal CentOS 6 installation.
    In this example, we'll use the Repoforge repository.

    ```bash
    rpm -ivh http://pkgs.repoforge.org/rpmforge-release/rpmforge-release-0.5.3-1.el6.rf.x86_64.rpm
    ```

    Enable the Extra repo

    ```bash
    vi /etc/yum.repos.d/rpmforge.repo
    ```

    Install rrdtool

    ```bash
    yum update rrdtool
    ```

    Disable the [rpmforge] and [rpmforge-extras] repos again

    ```bash
    vi /etc/yum.repos.d/rpmforge.repo
    ```

    Edit the rrdcached config `/etc/sysconfig/rrdcached`:

    ```bash
    # Settings for rrdcached
    OPTIONS="-w 1800 -z 1800 -f 3600 -s librenms -U librenms -G librenms -B -R -j /var/tmp -l unix:/run/rrdcached.sock -t 4 -F -b /opt/librenms/rrd/"
    RRDC_USER=librenms
    ```

    ```bash
    mkdir /var/run/rrdcached
    chown librenms:librenms /var/run/rrdcached/
    chown librenms:librenms /var/rrdtool/
    chown librenms:librenms /var/rrdtool/rrdcached/
    ```

    ```bash
    chkconfig rrdcached on
    ```

    Restart rrdcached

    ```bash
    service rrdcached start
    ```

### Network RRDCached

For remote RRDCached server make sure you have network option `-L` in `/var/default/rrdcached` or `rrdcached.unit`

=== "Debian/Ubuntu"

    Edit `/etc/default/rrdcached` to include:
    ```bash
    NETWORK_OPTIONS="-L"
    ```

## LibreNMS config

Edit your LibreNMS config by running the following:

=== "Local RRDCached"

    !!! setting "poller/rrdtool"
        ```bash
        lnms config:set rrdcached "unix:/run/rrdcached.sock"
        ```

=== "Network RRDCached"

    !!! setting "poller/rrdtool"
        ```bash
        lnms config:set rrdcached "${IPADDRESS}:42217"
        ```
    !!! note
        Change `${IPADDRESS}` to the ip the rrdcached server is listening on.

## Verify

Check to see if the graphs are being drawn in LibreNMS. This might take a few minutes.
After at least one poll cycle (5 mins), check the LibreNMS disk I/O performance delta.
Disk I/O can be found under the menu Devices>All Devices>[localhost_hostname]>Health>Disk I/O.

Depending on many factors, you should see the Ops/sec drop by ~30-40%.

### Verify SELINUX

If you are using SELinux, and you have issue you can verify the policy module is installed by running the following command:

```bash
semodule -l | grep rrdcached_librenms
```

Test Functionality: Ensure LibreNMS can successfully interact with RRDcached without SELinux denials. Check SELinux logs for any denials:

```bash
ausearch -m avc -ts recent
```

If there are no denials, the policy module has been successfully installed and Librenms can interact with RRDcached.

## Securing RRCached

According to the [man page](https://linux.die.net/man/1/rrdcached),
under "SECURITY CONSIDERATIONS", rrdcached has no authentication or security except for running under a unix socket. If you choose to use a network socket instead of a unix socket, you will need to secure your rrdcached installation. To do so you can proxy rrdcached using
nginx to allow only specific IPs to connect.

Using the same setup above, using nginx version 1.9.0 or later, you can follow this setup to proxy the default rrdcached port to the local unix socket.

(You can use `./conf.d` for your configuration as well)

`mkdir /etc/nginx/streams-{available,enabled}`

add the following to your nginx.conf file:

```nginx
#/etc/nginx/nginx.conf
...u
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

Replace `$LibreNMS_IP` with the ip of the server that will be using rrdcached. You can specify more than one `allow` statement. This will bind nginx to TCP 42217 (the default rrdcached port), allow the specified IPs to connect, and deny all others.

next, we'll symlink the config to streams-enabled:
`ln -s /etc/nginx/streams-{available,enabled}/rrd`

and reload nginx
`service nginx reload`
