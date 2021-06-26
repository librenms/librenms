source: Extensions/Applications.md
path: blob/master/doc/

# Introduction

You can use Application support to graph performance statistics of
many applications.

Different applications support a variety of ways to collect data: 1)
by direct connection to the application, 2) snmpd extend, or 3) [the
agent](Agent-Setup.md). The monitoring of applications could be added
before or after the hosts have been added to LibreNMS.

If multiple methods of collection are listed you only need to enable one.

## SNMP Extend

When using the snmp extend method, the application discovery module
will pick up which applications you have set up for monitoring
automatically, even if the device is already in LibreNMS. The
application discovery module is enabled by default for most \*nix
operating systems, but in some cases you will need to manually enable
the application discovery module.

### SUDO

One major thing to keep in mind when using SNMP extend is these run as the snmpd
user that can be an unprivileged user. In these situations you need to use sudo.

To test if you need sudo, first check the user snmpd is running as.
Then test if you can run the extend script as that user without issue.
For example if snmpd is running as 'Debian-snmp' and we want
to run the extend for proxmox, we check that the following run without error:

```
sudo -u Debian-snmp /usr/local/bin/proxmox
```

If it doesn't work, then you will need to use sudo with the extend command.
For the example above, that would mean adding the line below to the sudoers file:

```
Debian-snmp ALL = NOPASSWD: /usr/local/bin/proxmox
```

Finally we would need to add sudo to the extend command, which would look
like that for proxmox:

```
extend proxmox /usr/bin/sudo /usr/local/bin/proxmox
```

## Enable the application discovery module

1. Edit the device for which you want to add this support
1. Click on the *Modules* tab and enable the `applications` module.
1. This will be automatically saved, and you should get a green
   confirmation pop-up message.

![Enable-application-module](/img/Enable_application_module.png)

After you have enabled the application module, it would be wise to
then also enable which applications you want to monitor, in the rare
case where LibreNMS does not automatically detect it.

**Note**: Only do this if an application was not auto-discovered by
LibreNMS during discovery and polling.

## Enable the application(s) to be discovered

1. Go to the device you have just enabled the application module for.
1. Click on the *Applications* tab and select the applications you
   want to monitor.
1. This will also be automatically saved, and you should get a green
   confirmation pop-up message.

![Enable-applications](/img/Enable_applications.png)

## Agent

The unix-agent does not have a discovery module, only a poller
module. That poller module is always disabled by default. It needs to
be manually enabled if using the agent. Some applications will be
automatically enabled by the unix-agent poller module. It is better to
ensure that your application is enabled for monitoring. You can check
by following the steps under the `SNMP Extend` heading.

1. [Apache](#apache) - SNMP extend, Agent
1. [Asterisk](#asterisk) - SNMP extend
1. [backupninja](#backupninja) - SNMP extend
1. [BIND9/named](#bind9-aka-named) - SNMP extend, Agent
1. [Certificate](#certificate) - Certificate extend
1. [C.H.I.P.](#chip) - SNMP extend
1. [DHCP Stats](#dhcp-stats) - SNMP extend
1. [Docker Stats](#docker-stats) - SNMP extend
1. [Entropy](#entropy) - SNMP extend
1. [EXIM Stats](#exim-stats) - SNMP extend
1. [Fail2ban](#fail2ban) - SNMP extend
1. [FreeBSD NFS Client](#freebsd-nfs-client) - SNMP extend
1. [FreeBSD NFS Server](#freebsd-nfs-server) - SNMP extend
1. [FreeRADIUS](#freeradius) - SNMP extend, Agent
1. [Freeswitch](#freeswitch) - SNMP extend, Agent
1. [GPSD](#gpsd) - SNMP extend, Agent
1. [Icecast](#icecast) - SNMP extend, Agent
1. [Mailcow-dockerized postfix](#mailcow-dockerized-postfix) - SNMP extend
1. [Mailscanner](#mailscanner) - SNMP extend
1. [Mdadm](#mdadm) - SNMP extend
1. [Memcached](#memcached) - SNMP extend
1. [Munin](#munin) - Agent
1. [MySQL](#mysql) - SNMP extend, Agent
1. [NGINX](#nginx) - SNMP extend, Agent
1. [NFS Server](#nfs-server) - SNMP extend
1. [NTP Client](#ntp-client) - SNMP extend
1. [NTP Server/NTPD](#ntp-server-aka-ntpd) - SNMP extend
1. [Nvidia GPU](#nvidia-gpu) - SNMP extend
1. [Open Grid Scheduler](#open-grid-scheduler) - SNMP extend
1. [Opensips](#opensips) - SNMP extend
1. [OS Updates](#os-updates) - SNMP extend
1. [PHP-FPM](#php-fpm) - SNMP extend
1. [Pi-hole](#pi-hole) - SNMP extend
1. [Portactivity](#portactivity) - SNMP extend
1. [Postfix](#postfix) - SNMP extend
1. [Postgres](#postgres) - SNMP extend
1. [PowerDNS](#powerdns) - Agent
1. [PowerDNS Recursor](#powerdns-recursor) - Direct, SNMP extend, Agent
1. [PowerDNS dnsdist](#powerdns-dnsdist) - SNMP extend
1. [PowerMon](#powermon) - SNMP extend
1. [Proxmox](#proxmox) - SNMP extend
1. [Puppet Agent](#puppet-agent) - SNMP extend
1. [PureFTPd](#pureftpd) - SNMP extend
1. [Raspberry PI](#raspberry-pi) - SNMP extend
1. [Redis](#redis) - SNMP extend
1. [RRDCached](#rrdcached) - SNMP extend
1. [SDFS info](#sdfs-info) - SNMP extend
1. [Seafile](#seafile) - SNMP extend
1. [SMART](#smart) - SNMP extend
1. [Squid](#squid) - SNMP proxy
1. [TinyDNS/djbdns](#tinydns-aka-djbdns) - Agent
1. [Unbound](#unbound) - SNMP extend, Agent
1. [UPS-nut](#ups-nut) - SNMP extend
1. [UPS-apcups](#ups-apcups) - SNMP extend
1. [Voip-monitor](#voip-monitor) - SNMP extend
1. [ZFS](#zfs) - SNMP extend

# Apache

Either use SNMP extend or use the agent.

Note that you need to install and configure the Apache
[mod_status](https://httpd.apache.org/docs/2.4/en/mod/mod_status.html)
module before trying the script.

## SNMP Extend

1: Download the script onto the desired host (the host must be added
to LibreNMS devices)

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/apache-stats.py -O /etc/snmp/apache-stats.py
```

2: Make the script executable (chmod +x /etc/snmp/apache-stats.py)

3: Create the cache directory, '/var/cache/librenms/' and make sure
that it is owned by the user running the SNMP daemon.

```
mkdir -p /var/cache/librenms/
```

4: Verify it is working by running /etc/snmp/apache-stats.py Package `urllib3` for python3 needs to be
installed. In Debian-based systems for example you can achieve this by issuing:

```
apt-get install python3-urllib3
```

5: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend apache /etc/snmp/apache-stats.py
```

6: Restart snmpd on your host

7: Test by running

```
snmpwalk <various options depending on your setup> localhost NET-SNMP-EXTEND-MIB::nsExtendOutput2Table
```

## Agent

[Install the agent](Agent-Setup.md) on this device if it isn't already
and copy the `apache` script to `/usr/lib/check_mk_agent/local/`

1: Verify it is working by running /usr/lib/check_mk_agent/local/apache
(If you get error like "Can't locate LWP/Simple.pm". libwww-perl needs
to be installed: apt-get install libwww-perl)

2: Create the cache directory, '/var/cache/librenms/' and make sure
that it is owned by the user running the SNMP daemon.

```
mkdir -p /var/cache/librenms/
```

3: On the device page in Librenms, edit your host and check the
`Apache` under the Applications tab.

# Asterisk

A small shell script that reports various Asterisk call status.

## SNMP Extend

1: Download the [asterisk
script](https://github.com/librenms/librenms-agent/blob/master/snmp/asterisk)
to `/etc/snmp/` on your asterisk server.
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/asterisk -O /etc/snmp/asterisk
```

2: Run `chmod +x /etc/snmp/asterisk`

3: Configure `ASCLI` in the script.

4: Verify it is working by running `/etc/snmp/asterisk`

5: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend asterisk /etc/snmp/asterisk
```

6: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# backupninja

A small shell script that reports status of last backupninja backup.

## SNMP Extend

1: Download the [backupninja
script](https://github.com/librenms/librenms-agent/blob/master/snmp/backupninja.py)
to `/etc/snmp/backupninja.py` on your backuped server.
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/backupninja.py -O /etc/snmp/backupninja.py`
```
2: Make the script executable: `chmod +x /etc/snmp/backupninja.py`
3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend backupninja /etc/snmp/backupninja.py
```

4: Restart snmpd on your host


# BIND9 aka named

1: Create stats file with appropriate permissions:

```bash
~$ touch /var/cache/bind/stats
~$ chown bind:bind /var/cache/bind/stats
```

Change `user:group` to the user and group that's running bind/named.

2: Bind/named configuration:

```text
options {
    ...
    statistics-file "/var/cache/bind/stats";
    zone-statistics yes;
    ...
};
```

3: Restart your bind9/named after changing the configuration.

4: Verify that everything works by executing `rndc stats && cat
/var/cache/bind/stats`. In case you get a `Permission Denied` error,
make sure you changed the ownership correctly.

5: Also be aware that this file is appended to each time `rndc stats`
is called. Given this it is suggested you setup file rotation for
it. Alternatively you can also set zero_stats to 1 in the config.

6: The script for this also requires the Perl module
`File::ReadBackwards`.

```
FreeBSD       => p5-File-ReadBackwards
CentOS/RedHat => perl-File-ReadBackwards
Debian/Ubuntu => libfile-readbackwards-perl
```

If it is not available, it can be installed by `cpan -i File::ReadBackwards`.

7: You may possibly need to configure the agent/extend script as well.

The config file's path defaults to the same path as the script, but
with .config appended. So if the script is located at
`/etc/snmp/bind`, the config file will be
`/etc/snmp/bind.config`. Alternatively you can also specify a config
via `-c $file`.

Anything starting with a # is comment. The format for variables are
$variable=$value. Empty lines are ignored. Spaces and tabs at either
the start or end of a line are ignored.

Content of an example /etc/snmp/bind.config . Please edit with your
own settings.

```
rndc = The path to rndc. Default: /usr/bin/env rndc
call_rndc = A 0/1 boolean on whether or not to call rndc stats.
    Suggest to set to 0 if using netdata. Default: 1
stats_file = The path to the named stats file. Default: /var/cache/bind/stats
agent = A 0/1 boolean for if this is being used as a LibreNMS
    agent or not. Default: 0
zero_stats = A 0/1 boolean for if the stats file should be zeroed
    first. Default: 0 (1 if guessed)
```

If you want to guess at the configuration, call the script with `-g`
and it will print out what it thinks it should be.

## SNMP Extend

1: Copy the bind shell script, to the desired host.

```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/bind -O /etc/snmp/bind
```

2: Make the script executable

```
chmod +x /etc/snmp/bind
```

3: Edit your snmpd.conf file and add:

```
extend bind /etc/snmp/bind
```

4: Restart snmpd on the host in question.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

## Agent

1: [Install the agent](Agent-Setup.md) on this device if it isn't
already and copy the script to `/usr/lib/check_mk_agent/local/bind`
via `wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/bind -O /usr/lib/check_mk_agent/local/bind`

2: Run `chmod +x /usr/lib/check_mk_agent/local/bind`

3: Set the variable 'agent' to '1' in the config.

# Certificate

A small python3 script that checks age and remaining validity of certificates

This script needs following packages on Debian/Ubuntu Systems:

* python3
* python3-openssl

Content of an example /etc/snmp/certificate.json . Please edit with your own settings.
```
{"domains": [
    {"fqdn": "www.mydomain.com"},
    {"fqdn": "some.otherdomain.org",
     "port": 8443},
    {"fqdn": "personal.domain.net"}
]
}
```
Key 'domains' contains a list of domains to check.
Optional you can define a port. By default it checks on port 443.

## SNMP Extend
1. Copy the shell script to the desired host.
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/certificate.py -O /etc/snmp/certificate.py
```

2. Run `chmod +x /etc/snmp/certificate.py`

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend certificate /etc/snmp/certificate.py
```
4. Restart snmpd on your host

The application should be auto-discovered as described at the top of the page. If it is not, please follow the steps set out under `SNMP Extend` heading top of page.

# C.H.I.P

C.H.I.P. is a $9 R8 based tiny computer ideal for small projects.
Further details: <https://getchip.com/pages/chip>

1: Copy the shell script to the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/chip.sh -O /etc/snmp/power-stat.sh
```

2: Run `chmod +x /etc/snmp/power-stat.sh`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend power-stat /etc/snmp/power-stat.sh
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# DHCP Stats

A small python3 script that reports current DHCP leases stats and pool usage.

Also you have to install the dhcpd-pools Package.
Under Ubuntu/Debian just run `apt install dhcpd-pools`

## SNMP Extend

1: Copy the shell script to the desired host.

```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/dhcp.py -O /etc/snmp/dhcp.py
```

2: Run `chmod +x /etc/snmp/dhcp.py`


3: edit a config file:

Content of an example /etc/snmp/dhcp.json . Please edit with your own settings.
```
{"leasefile": "/var/lib/dhcp/dhcpd.leases"
}
```
Key 'leasefile' specifies the path to your lease file.

4: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend dhcpstats /etc/snmp/dhcp.py
```

5: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Docker Stats

It allows you to know which container docker run and their stats.

This script require: jq

## SNMP Extend

1: Install jq
```
sudo apt install jq
```

2: Copy the shell script to the desired host.

```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/docker-stats.sh -O /etc/snmp/docker-stats.sh
```

3: Run `chmod +x /etc/snmp/docker-stats.sh`


4: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend docker /etc/snmp/docker-stats.sh
```

5: Restart snmpd on your host
```
systemctl restart snmpd
```

# Entropy

A small shell script that checks your system's available random entropy.

## SNMP Extend

1: Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/entropy.sh -O /etc/snmp/entropy.sh
```

2: Run `chmod +x /etc/snmp/entropy.sh`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend entropy /etc/snmp/entropy.sh
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# EXIM Stats

SNMP extend script to get your exim stats data into your host.

## SNMP Extend

1: Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/exim-stats.sh -O /etc/snmp/exim-stats.sh
```

2: Run `chmod +x /etc/snmp/exim-stats.sh`

3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend exim-stats /etc/snmp/exim-stats.sh
```

4: If you are using sudo edit your sudo users (usually `visudo`) and
add at the bottom:

```
snmp ALL=(ALL) NOPASSWD: /etc/snmp/exim-stats.sh, /usr/bin/exim*
```

5: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Fail2ban

## SNMP Extend

1: Copy the shell script, fail2ban, to the desired host.

```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/fail2ban -O /etc/snmp/fail2ban
```

2: Run `chmod +x /etc/snmp/fail2ban`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend fail2ban /etc/snmp/fail2ban
```

If you want to use the cache, it is as below, by using the -c switch.

```
extend fail2ban /etc/snmp/fail2ban -c
```

If you want to use the cache and update it if needed, this can by
using the -c and -U switches.

```
extend fail2ban /etc/snmp/fail2ban -c -U
```

If you need to specify a custom location for the fail2ban-client, that
can be done via the -f switch.

If not specified, "/usr/bin/env fail2ban-client" is used.

```
extend fail2ban /etc/snmp/fail2ban -f /foo/bin/fail2ban-client
```

5: Restart snmpd on your host

6: If you wish to use caching, add the following to /etc/crontab and
restart cron.

```
*/3    *    *    *    *    root    /etc/snmp/fail2ban -u
```

7: Restart or reload cron on your system.

If you have more than a few jails configured, you may need to use
caching as each jail needs to be polled and fail2ban-client can't do
so in a timely manner for than a few. This can result in failure of
other SNMP information being polled.

For additional details of the switches, please see the POD in the
script it self at the top.

# FreeBSD NFS Client

## SNMP Extend

1: Copy the shell script, fbsdnfsserver, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/fbsdnfsclient
-O /etc/snmp/fbsdnfsclient`

2: Run `chmod +x /etc/snmp/fbsdnfsclient`

3: Edit your snmpd.conf file and add:

```
extend fbsdnfsclient /etc/snmp/fbsdnfsclient
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# FreeBSD NFS Server

## SNMP Extend

1: Copy the shell script, fbsdnfsserver, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/fbsdnfsserver
-O /etc/snmp/fbsdnfsserver`

2: Run `chmod +x /etc/snmp/fbsdnfsserver`

3: Edit your snmpd.conf file and add:

```
extend fbsdnfsserver /etc/snmp/fbsdnfsserver
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# FreeRADIUS

The FreeRADIUS application extension requires that status_server be
enabled in your FreeRADIUS config.  For more information see:
<https://wiki.freeradius.org/config/Status>

You should note that status requests increment the FreeRADIUS request
stats.  So LibreNMS polls will ultimately be reflected in your
stats/charts.

1: Go to your FreeRADIUS configuration directory (usually /etc/raddb
or /etc/freeradius).

2: `cd sites-enabled`

3: `ln -s ../sites-available/status status`

4: Restart FreeRADIUS.

5: You should be able to test with the radclient as follows...

```
echo "Message-Authenticator = 0x00, FreeRADIUS-Statistics-Type = 31, Response-Packet-Type = Access-Accept" | \
radclient -x localhost:18121 status adminsecret
```

Note that adminsecret is the default secret key in status_server.
Change if you've modified this.

## SNMP Extend

1: Copy the freeradius shell script, to the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/freeradius.sh -O /etc/snmp/freeradius.sh
```

2: Run `chmod +x /etc/snmp/freeradius.sh`

3: If you've made any changes to the FreeRADIUS status_server config
(secret key, port, etc.) edit freeradius.sh and adjust the config
variable accordingly.

4: Edit your snmpd.conf file and add:

```
extend freeradius /etc/snmp/freeradius.sh
```

5: Restart snmpd on the host in question.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

## Agent

1: [Install the agent](Agent-Setup.md) on this device if it isn't
already and copy the script to
`/usr/lib/check_mk_agent/local/freeradius.sh` via `wget
https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/freeradius.sh
-O /usr/lib/check_mk_agent/local/freeradius.sh`

2: Run `chmod +x /usr/lib/check_mk_agent/local/freeradius.sh`

3: If you've made any changes to the FreeRADIUS status_server config
(secret key, port, etc.) edit freeradius.sh and adjust the config
variable accordingly.

4: Edit the freeradius.sh script and set the variable 'AGENT' to '1'
in the config.

# Freeswitch

A small shell script that reports various Freeswitch call status.

## Agent

1: [Install the agent](Agent-Setup.md) on your Freeswitch server if it
isn't already

2: Copy the [freeswitch
script](https://github.com/librenms/librenms-agent/blob/master/agent-local/freeswitch)
to `/usr/lib/check_mk_agent/local/`

3: Configure `FSCLI` in the script. You may also have to create an
`/etc/fs_cli.conf` file if your `fs_cli` command requires
authentication.

4: Verify it is working by running `/usr/lib/check_mk_agent/local/freeswitch`

## SNMP Extend

1: Copy the [freeswitch
script](https://github.com/librenms/librenms-agent/blob/master/agent-local/freeswitch)
to `/etc/snmp/` on your Freeswitch server.

2: Run `chmod +x /etc/snmp/freeswitch`

3: Configure `FSCLI` in the script. You may also have to create an
`/etc/fs_cli.conf` file if your `fs_cli` command requires
authentication.

4: Verify it is working by running `/etc/snmp/freeswitch`

5: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend freeswitch /etc/snmp/freeswitch
```

6: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# GPSD

## SNMP Extend

1: Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/gpsd -O /etc/snmp/gpsd
```

2: Run `chmod +x /etc/snmp/gpsd`

3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend gpsd /etc/snmp/gpsd
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading at the top of the page.

## Agent

[Install the agent](Agent-Setup.md) on this device if it isn't already
and copy the `gpsd` script to `/usr/lib/check_mk_agent/local/`

You may need to configure `$server` or `$port`.

Verify it is working by running `/usr/lib/check_mk_agent/local/gpsd`

# Icecast

Shell script that reports load average/memory/open-files stats of Icecast
## SNMP Extend

1. Copy the shell script, icecast-stats.sh, to the desired host (the host must be added to LibreNMS devices)
```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/icecast-stats.sh -O /etc/snmp/icecast-stats.sh
```

2: Make the script executable `chmod +x /etc/snmp/icecast-stats.sh`

3. Verify it is working by running `/etc/snmp/icecast-stats.sh`

4: Edit your snmpd.conf file (usually `/etc/snmp/icecast-stats.sh`) and add:

```
extend icecast /etc/snmp/icecast-stats.sh
```
# mailcow-dockerized postfix

## SNMP Extend

1: Download the script into the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mailcow-dockerized-postfix -O /etc/snmp/mailcow-dockerized-postfix
```

2: Run `chmod +x /etc/snmp/mailcow-dockerized-postfix`

> Maybe you will be neeed to install `pflogsumm` on debian based OS. Please check if you have package installed.

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend mailcow-postfix /etc/snmp/mailcow-dockerized-postfix
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Mailscanner

## SNMP Extend

1: Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mailscanner.php -O /etc/snmp/mailscanner.php
```

2: Run `chmod +x /etc/snmp/mailscanner.php`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend mailscanner /etc/snmp/mailscanner.php
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Mdadm

This shell script checks mdadm health and array data

## SNMP Extend

1: Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mdadm -O /etc/snmp/mdadm
```

2: Run `chmod +x /etc/snmp/mdadm`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend mdadm /etc/snmp/mdadm
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the
top of the page. If it is not, please follow the steps set out
under `SNMP Extend` heading top of page.

# Memcached

## SNMP Extend

1: Copy the [memcached
   script](https://github.com/librenms/librenms-agent/blob/master/agent-local/memcached)
   to `/etc/snmp/` on your remote server.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/memcached -O /etc/snmp/memcached
```

2: Make the script executable: `chmod +x /etc/snmp/memcached`

3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend memcached /etc/snmp/memcached
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Munin

## Agent

1. Install the script to your agent: `wget
   https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/munin
   -O /usr/lib/check_mk_agent/local/munin`
1. Make the script executable (`chmod +x
   /usr/lib/check_mk_agent/local/munin`)
1. Create the munin scripts dir: `mkdir -p
   /usr/share/munin/munin-scripts`
1. Install your munin scripts into the above directory.

To create your own custom munin scripts, please see this example:

```bash
#!/bin/bash
if [ "$1" = "config" ]; then
    echo 'graph_title Some title'
    echo 'graph_args --base 1000 -l 0' #not required
    echo 'graph_vlabel Some label'
    echo 'graph_scale no' #not required, can be yes/no
    echo 'graph_category system' #Choose something meaningful, can be anything
    echo 'graph_info This graph shows something awesome.' #Short desc
    echo 'foobar.label Label for your unit' # Repeat these two lines as much as you like
    echo 'foobar.info Desc for your unit.'
    exit 0
fi
echo -n "foobar.value " $(date +%s) #Populate a value, here unix-timestamp
```

# MySQL

Create the cache directory, '/var/cache/librenms/' and make sure
that it is owned by the user running the SNMP daemon.

```
mkdir -p /var/cache/librenms/
```

The MySQL script requires PHP-CLI and the PHP MySQL extension, so
please verify those are installed.

CentOS (May vary based on PHP version)

```
yum install php-cli php-mysql
```

Debian (May vary based on PHP version)

```
apt-get install php-cli php-mysql
```

Unlike most other scripts, the MySQL script requires a configuration
file `mysql.cnf` in the same directory as the extend or agent script
with following content:

```php
<?php
$mysql_user = 'root';
$mysql_pass = 'toor';
$mysql_host = 'localhost';
$mysql_port = 3306;
```

Note that depending on your MySQL installation (chrooted install for example),
you may have to specify 127.0.0.1 instead of localhost. Localhost make
a MySQL connection via the mysql socket, while 127.0.0.1 make a standard
IP connection to mysql.

Note also if you get a mysql error `Uncaught TypeError: mysqli_num_rows(): Argument #1`,
this is because you are using a newer mysql version which doesnt support `UNBLOCKING` for slave statuses,
so you need to also include the line `$chk_options['slave'] = false;` into `mysql.cnf` to skip checking slave statuses

## Agent

[Install the agent](Agent-Setup.md) on this device if it isn't already

and copy the `mysql` script to `/usr/lib/check_mk_agent/local/`

Verify it is working by running `/usr/lib/check_mk_agent/local/mysql`

## SNMP extend

1: Copy the mysql script to the desired host.
```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/mysql -O /etc/snmp/mysql
```

2: Make the file executable
```
chmod +x /etc/snmp/mysql
```

3: Edit your snmpd.conf file and add:
```
extend mysql /etc/snmp/mysql
```

4: Restart snmpd.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# NGINX

NGINX is a free, open-source, high-performance HTTP server: <https://www.nginx.org/>

It's required to have the following directive in your nginx
configuration responsible for the localhost server:

```text
location /nginx-status {
    stub_status on;
    access_log  off;
    allow 127.0.0.1;
    allow ::1;
    deny  all;
}
```

## SNMP Extend

1: Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/nginx -O /etc/snmp/nginx
```

2: Run `chmod +x /etc/snmp/nginx`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend nginx /etc/snmp/nginx
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

## Agent

[Install the agent](Agent-Setup.md) on this device if it isn't already
and copy the `nginx` script to `/usr/lib/check_mk_agent/local/`

# NFS Server

Export the NFS stats from as server.

## SNMP Extend

1: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add :

```
extend nfs-server /bin/cat /proc/net/rpc/nfsd
```

note : find out where cat is located using : `which cat`

2: reload snmpd service to activate the configuration

# NTP Client

A shell script that gets stats from ntp client.

## SNMP Extend

1: Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/ntp-client -O /etc/snmp/ntp-client
```

2: Run `chmod +x /etc/snmp/ntp-client`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend ntp-client /etc/snmp/ntp-client
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# NTP Server aka NTPD

A shell script that gets stats from ntp server (ntpd).

## SNMP Extend

1. Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/ntp-server.sh -O /etc/snmp/ntp-server.sh
```

2: Run `chmod +x /etc/snmp/ntp-server.sh`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend ntp-server /etc/snmp/ntp-server.sh
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Nvidia GPU

## SNMP Extend

1: Copy the shell script, nvidia, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/nvidia -O
/etc/snmp/nvidia`

2: Run `chmod +x /etc/snmp/nvidia`

3: Edit your snmpd.conf file and add:

```
extend nvidia /etc/snmp/nvidia
```

5: Restart snmpd on your host.

6: Verify you have nvidia-smi installed, which it generally should be
if you have the driver from Nvida installed.

The GPU numbering on the graphs will correspond to how the nvidia-smi
sees them as being.

For questions about what the various values are/mean, please see the
nvidia-smi man file under the section covering dmon.

# Open Grid Scheduler

Shell script to track the OGS/GE jobs running on clusters.

## SNMP Extend

1: Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/rocks.sh -O /etc/snmp/rocks.sh
```

2: Run `chmod +x /etc/snmp/rocks.sh`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend ogs /etc/snmp/rocks.sh
```

4: Restart snmpd.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Opensips

Script that reports load-average/memory/open-files stats of Opensips

## SNMP Extend

1: Download the script onto the desired host. `wget
   https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/opensips-stats.sh
   -O /etc/snmp/opensips-stats.sh`

2: Make the script executable: `chmod +x /etc/snmp/opensips-stats.sh`

3. Verify it is working by running `/etc/snmp/opensips-stats.sh`

3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend opensips /etc/snmp/opensips-stats.sh
```

# OS Updates

A small shell script that checks your system package manager for any
available updates. Supports apt-get/pacman/yum/zypper package
managers.

For pacman users automatically refreshing the database, it is
recommended you use an alternative database location
`--dbpath=/var/lib/pacman/checkupdate`

## SNMP Extend

1: Download the script onto the desired host.

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/osupdate -O /etc/snmp/osupdate
```

2: Run `chmod +x /etc/snmp/osupdate`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend osupdate /etc/snmp/osupdate
```

4: Restart snmpd on your host

_Note_: apt-get depends on an updated package index. There are several
ways to have your system run `apt-get update` automatically. The
easiest is to create `/etc/apt/apt.conf.d/10periodic` and pasting the
following in it: `APT::Periodic::Update-Package-Lists "1";`. If you
have apticron, cron-apt or apt-listchanges installed and configured,
chances are that packages are already updated periodically .

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# PHP-FPM

## SNMP Extend

1: Copy the shell script, phpfpmsp, to the desired host. `wget
   https://github.com/librenms/librenms-agent/raw/master/snmp/phpfpmsp
   -O /etc/snmp/phpfpmsp`

2: Run `chmod +x /etc/snmp/phpfpmsp`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend phpfpmsp /etc/snmp/phpfpmsp
```

5: Edit /etc/snmp/phpfpmsp to include the status URL for the PHP-FPM
   pool you are monitoring.

6: Restart snmpd on your host

It is worth noting that this only monitors a single pool. If you want
to monitor multiple pools, this won't do it.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Pi-hole

## SNMP Extend

1: Copy the shell script, pi-hole, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/pi-hole -O
/etc/snmp/pi-hole`

2: Run `chmod +x /etc/snmp/pi-hole`

3: Edit your snmpd.conf file and add:

```
extend pi-hole /etc/snmp/pi-hole
```

4: To get all data you must get your API auth token from Pi-hole
server and change the API_AUTH_KEY entry inside the snmp script.

5: Restard snmpd.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Portactivity

## SNMP Extend

Ubuntu is shown below.

```
apt install libparse-netstat-perl
apt install libjson-perl
```

2: Copy the Perl script to the desired host (the host must be added to
   LibreNMS devices)

```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/portactivity -O /etc/snmp/portactivity
```

3: Make the script executable. (chmod +x /etc/snmp/portactivity)

4: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend portactivity /etc/snmp/portactivity -p http,ldap,imap
```

Will monitor HTTP, LDAP, and IMAP. The -p switch specifies what ports
to use. This is a comma seperated list.

These must be found in '/etc/services' or where ever NSS is set to
fetch it from. If not, it will throw an error.

If you want to JSON returned by it to be printed in a pretty format
use the -P flag.

5: Restart snmpd on your host.

Please note that for only TCP[46] services are supported.

# Postfix

## SNMP Extend

1: Copy the shell script, postfix-queues, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/postfix-queues
-O /etc/snmp/postfix-queues`

2: Copy the Perl script, postfixdetailed, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/postfixdetailed
-O /etc/snmp/postfixdetailed`

3: Make both scripts executable. Run `chmod +x
/etc/snmp/postfixdetailed /etc/snmp/postfix-queues`

4: Edit your snmpd.conf file and add:

```
extend mailq /etc/snmp/postfix-queues
extend postfixdetailed /etc/snmp/postfixdetailed
```

5: Restart snmpd.

6: Install pflogsumm for your OS.

7: Make sure the cache file in /etc/snmp/postfixdetailed is some place
that snmpd can write too. This file is used for tracking changes
between various values between each time it is called by snmpd. Also
make sure the path for pflogsumm is correct.

8: Run /etc/snmp/postfixdetailed to create the initial cache file so
you don't end up with some crazy initial starting value. Please note
that each time /etc/snmp/postfixdetailed is ran, the cache file is
updated, so if this happens in between LibreNMS doing it then the
values will be thrown off for that polling period.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

> NOTE: If using RHEL for your postfix server, qshape must be
> installed manually as it is not officially supported. CentOs 6 rpms
> seem to work without issues.

# Postgres

## SNMP Extend

1: Copy the shell script, postgres, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/postgres -O
/etc/snmp/postgres`

2: Run `chmod +x /etc/snmp/postgres`

3: Edit your snmpd.conf file and add:

```
extend postgres /etc/snmp/postgres
```

4: Restart snmpd on your host

5: Install the Nagios check check_postgres.pl on your system:
<https://github.com/bucardo/check_postgres>

6: Verify the path to check_postgres.pl in /etc/snmp/postgres is
correct.

7: If you wish it to ignore the database postgres for totalling up the
stats, set ignorePG to 1(the default) in /etc/snmp/postgres. If you
are using netdata or the like, you may wish to set this or otherwise
that total will be very skewed on systems with light or moderate usage.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# PowerDNS

An authoritative DNS server: <https://www.powerdns.com/auth.html>

## SNMP Extend

1: Copy the shell script, powerdns.py, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/powerdns.py
-O /etc/snmp/powerdns.py`

2: Run `chmod +x /etc/snmp/powerdns.py`

3: Edit your snmpd.conf file and add:

```
extend powerdns /etc/snmp/powerdns.py
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

## Agent

[Install the agent](Agent-Setup.md) on this device if it isn't already
and copy the `powerdns` script to `/usr/lib/check_mk_agent/local/`

# PowerDNS Recursor

A recursive DNS server: <https://www.powerdns.com/recursor.html>

## Direct

The LibreNMS polling host must be able to connect to port 8082 on the
monitored device. The web-server must be enabled, see the Recursor
docs: <https://doc.powerdns.com/md/recursor/settings/#webserver>

## Variables

`$config['apps']['powerdns-recursor']['api-key']` required, this is
defined in the Recursor config

`$config['apps']['powerdns-recursor']['port']` numeric, defines the
port to connect to PowerDNS Recursor on.  The default is 8082

`$config['apps']['powerdns-recursor']['https']` true or false,
defaults to use http.

## SNMP Extend

1: Copy the shell script, powerdns-recursor, to the desired
host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/powerdns-recursor
-O /etc/snmp/powerdns-recursor`

2: Run `chmod +x /etc/snmp/powerdns-recursor`

3: Edit your snmpd.conf file and add:

```
extend powerdns-recursor /etc/snmp/powerdns-recursor
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

## Agent

[Install the agent](Agent-Setup.md) on this device if it isn't already
and copy the `powerdns-recursor` script to
`/usr/lib/check_mk_agent/local/`

This script uses `rec_control get-all` to collect stats.

# PowerDNS-dnsdist

## SNMP Extend

1: Copy the BASH script to the desired host.

```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/powerdns-dnsdist -O /etc/snmp/powerdns-dnsdist
```

2: Make the script executable (chmod +x /etc/snmp/powerdns-dnsdist)

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend powerdns-dnsdist /etc/snmp/powerdns-dnsdist
```

4: Restart snmpd on your host.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# PowerMon

PowerMon tracks the power usage on your host and can report on both consumption
and cost, using a python script installed on the host.

[PowerMon consumption graph](../img/example-app-powermon-consumption-02.png)

Currently the script uses one of two methods to determine current power usage:

* ACPI via libsensors

* HP-Health (HP Proliant servers only)

The ACPI method is quite unreliable as it is usually only implemented by
battery-powered devices, e.g. laptops. YMMV. However, it's possible to support
any method as long as it can return a power value, usually in Watts.

> TIP: You can achieve this by adding a method and a function for that method to
> the script. It should be called by getData() and return a dictionary.

Because the methods are unreliable for all hardware, you need to declare to the
script which method to use. The are several options to assist with testing, see
`--help`.

## SNMP Extend

### Initial setup

1. Download the python script onto the host:
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/powermon-snmp.py -O /usr/local/bin/powermon-snmp.py
```

2. Make the script executable:
```
chmod +x /usr/local/bin/powermon-snmp.py
```

3. Edit the script and set the cost per kWh for your supply. You must uncomment
this line for the script to work:
```
vi /usr/local/bin/powermon-snmp.py
#costPerkWh = 0.15
```

4. Choose you method below:

    === "Method 1: sensors"

        * Install dependencies:
        ```
        dnf install lm_sensors
        pip install PySensors
        ```

        * Test the script from the command-line. For example:
        ```
        $ /usr/local/bin/powermon-snmp.py -m sensors -n -p
        {
          "meter": {
            "0": {
              "reading": 0.0
            }
          },
          "psu": {},
          "supply": {
            "rate": 0.15
          },
          "reading": "0.0"
        }
        ```

        If you see a reading of `0.0` it is likely this method is not supported for
        your system. If not, continue.

    === "Method 2: hpasmcli"

        * Obtain the hp-health package for your system. Generally there are
        three options:
            * Standalone package from [HPE Support](https://support.hpe.com/hpsc/swd/public/detail?swItemId=MTX-c0104db95f574ae6be873e2064#tab2)
            * From the HP Management Component Pack (MCP).
            * Included in the [HP Service Pack for Proliant (SPP)](https://support.hpe.com/hpesc/public/docDisplay?docId=emr_na-a00026884en_us)

        * If you've downloaded the standalone package, install it. For example:
        ```
        rpm -ivh hp-health-10.91-1878.11.rhel8.x86_64.rpm
        ```

        * Check the service is running:
        ```
        systemctl status hp-health
        ```

        * Test the script from the command-line. For example:
        ```
        $ /usr/local/bin/powermon-snmp.py -m hpasmcli -n -p
        {
          "meter": {
            "1": {
              "reading": 338.0
            }
          },
          "psu": {
            "1": {
              "present": "Yes",
              "redundant": "No",
              "condition": "Ok",
              "hotplug": "Supported",
              "reading": 315.0
            },
            "2": {
              "present": "Yes",
              "redundant": "No",
              "condition": "FAILED",
              "hotplug": "Supported"
            }
          },
          "supply": {
            "rate": 0.224931
          },
          "reading": 338.0
        }
        ```

        If you see a reading of `0.0` it is likely this method is not supported for
        your system. If not, continue.

    ### Finishing Up

5. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add the following:
```
extend  powermon   /usr/local/bin/powermon-snmp.py -m hpasmcli
```

    > NOTE: Avoid using other script options in the snmpd config as the results may not be
    > interpreted correctly by LibreNMS.

6. Reload your snmpd service:
```
systemctl reload snmpd
```

7. You're now ready to enable the application in LibreNMS.


# Proxmox

1: For Proxmox 4.4+ install the libpve-apiclient-perl package `apt
   install libpve-apiclient-perl`

2: Download the script onto the desired host (the host must be added
   to LibreNMS devices) `wget
   https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/proxmox
   -O /usr/local/bin/proxmox`

3: Run `chmod +x /usr/local/bin/proxmox`

4: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
   `extend proxmox /usr/local/bin/proxmox`

5: Note: if your snmpd doesn't run as root, you might have to invoke
   the script using sudo and modify the "extend" line

```
extend proxmox /usr/bin/sudo /usr/local/bin/proxmox
```

after, edit your sudo users (usually `visudo`) and add at the bottom:

```
Debian-snmp ALL=(ALL) NOPASSWD: /usr/local/bin/proxmox
```

6: Restart snmpd on your host

# Puppet Agent

SNMP extend script to get your Puppet Agent data into your host.

## SNMP Extend

1: Download the script onto the desired host. `wget
   https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/puppet_agent.py
   -O /etc/snmp/puppet_agent.py`

2: Make the script executable: `chmod +x /etc/snmp/puppet_agent.py`

3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend puppet-agent /etc/snmp/puppet_agent.py
```

The Script needs `python3-yaml` package to be installed.

Per default script searches for on of this files:

* /var/cache/puppet/state/last_run_summary.yaml
* /opt/puppetlabs/puppet/cache/state/last_run_summary.yaml

optionally you can add a specific summary file with creating `/etc/snmp/puppet.json`
```
{
     "agent": {
        "summary_file": "/my/custom/path/to/summary_file"
     }
}
```
custom summary file has highest priority

4: Restart snmpd on the host

# PureFTPd

SNMP extend script to monitor PureFTPd.

## SNMP Extend

1: Download the script onto the desired host. `wget
   https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/pureftpd.py
   -O /etc/snmp/pureftpd.py`

2: Make the script executable: `chmod +x /etc/snmp/pureftpd.py`

3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend pureftpd sudo /etc/snmp/pureftpd.py
```

4: Edit your sudo users (usually `visudo`) and add at the bottom:

```
snmp ALL=(ALL) NOPASSWD: /etc/snmp/pureftpd.py
```
or the path where your pure-ftpwho is located


5: If pure-ftpwho is not located in /usr/sbin

you will also need to create a config file, which is named

pureftpd.json. The file has to be located in /etc/snmp/.


```
{"pureftpwho_cmd": "/usr/sbin/pure-ftpwho"
}
```

5: Restart snmpd on your host

# Raspberry PI

SNMP extend script to get your PI data into your host.

## SNMP Extend

1: Download the script onto the desired host. `wget
   https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/raspberry.sh
   -O /etc/snmp/raspberry.sh`

2: Make the script executable: `chmod +x /etc/snmp/raspberry.sh`

3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend raspberry /usr/bin/sudo /bin/sh /etc/snmp/raspberry.sh
```

4: Edit your sudo users (usually `visudo`) and add at the bottom:

```
snmp ALL=(ALL) NOPASSWD: /bin/sh /etc/snmp/raspberry.sh
```

**Note:** If you are using Raspian, the default user is
`Debian-snmp`. Change `snmp` above to `Debian-snmp`. You can verify
the user snmpd is using with `ps aux | grep snmpd`

5: Restart snmpd on PI host

# Redis

SNMP extend script to monitor your Redis Server

## SNMP Extend

1: Download the script onto the desired host. `wget
   https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/redis.py
   -O /etc/snmp/redis.py`

2: Make the script executable: `chmod +x /etc/snmp/redis.py`

3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend redis /etc/snmp/redis.py
```

# RRDCached

Install/Setup:
For Install/Setup Local Librenms RRDCached: Please see [RRDCached](RRDCached.md)

Will collect stats by:
1: Connecting directly to the associated device on port 42217
2: Monitor thru snmp with SNMP extend, as outlined below
3: Connecting to the rrdcached server specified by the `rrdcached` setting

SNMP extend script to monitor your (remote) RRDCached via snmp

## SNMP Extend

1: Download the script onto the desired host. `wget
   https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/rrdcached
   -O /etc/snmp/rrdcached`

2: Make the script executable: `chmod +x /etc/snmp/rrdcached`

3: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```
extend rrdcached /etc/snmp/rrdcached
```

# SDFS info

A small shell script that exportfs SDFS volume info.

## SNMP Extend

1: Download the script onto the desired host (the host must be added
   to LibreNMS devices)

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/sdfsinfo -O /etc/snmp/sdfsinfo
```

2: Make the script executable (chmod +x /etc/snmp/sdfsinfo)

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend sdfsinfo /etc/snmp/sdfsinfo
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Seafile

SNMP extend script to monitor your Seafile Server

## SNMP Extend

1: Copy the Python script, seafile.py, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/seafile.py -O
/etc/snmp/seafile.py`

Also you have to install the requests Package for Python3.
Under Ubuntu/Debian just run `apt install python3-requests`

2: Run `chmod +x /etc/snmp/seafile.py`

3: Edit your snmpd.conf file and add:

```
extend seafile /etc/snmp/seafile.py
```

4: You will also need to create the config file, which is named
seafile.json . The script has to be located at /etc/snmp/.


```
{"url": "https://seafile.mydomain.org",
 "username": "some_admin_login@mail.address",
 "password": "password",
 "account_identifier": "name"
 "hide_monitoring_account": true
}
```

The variables are as below.

```
url = Url how to get access to Seafile Server
username = Login to Seafile Server.
           It is important that used Login has admin privileges.
           Otherwise most API calls will be denied.
password = Password to the configured login.
account_identifier = Defines how user accounts are listed in RRD Graph.
                     Options are: name, email
hide_monitoring_account = With this Boolean you can hide the Account which you
                          use to access Seafile API
```

**Note:**It is recommended to use a dedicated Administrator account for monitoring.

# SMART

## SNMP Extend

1. Copy the Perl script, smart, to the desired host.

```wget https://github.com/librenms/librenms-agent/raw/master/snmp/smart -O /etc/snmp/smart```

2. Run `chmod +x /etc/snmp/smart`

3. Edit your snmpd.conf file and add:

```
extend smart /etc/snmp/smart
```

4. You will also need to create the config file, which defaults to the same path as the script,
but with .config appended. So if the script is located at /etc/snmp/smart, the config file
will be `/etc/snmp/smart.config`. Alternatively you can also specific a config via `-c`.

Anything starting with a # is comment. The format for variables is $variable=$value. Empty
lines are ignored. Spaces and tabes at either the start or end of a line are ignored. Any
line with out a matched variable or # are treated as a disk.

```
#This is a comment
cache=/var/cache/smart
smartctl=/usr/bin/env smartctl
useSN=1
ada0
ada1
da5 /dev/da5 -d sat
twl0,0 /dev/twl0 -d 3ware,0
twl0,1 /dev/twl0 -d 3ware,1
twl0,2 /dev/twl0 -d 3ware,2
```

The variables are as below.

```
cache = The path to the cache file to use. Default: /var/cache/smart
smartctl = The path to use for smartctl. Default: /usr/bin/env smartctl
useSN = If set to 1, it will use the disks SN for reporting instead of the device name.
        1 is the default. 0 will use the device name.
```

A disk line is can be as simple as just a disk name under /dev/. Such as in the config above
The line "ada0" would resolve to "/dev/ada0" and would be called with no special argument. If
a line has a space in it, everything before the space is treated as the disk name and is what
used for reporting and everything after that is used as the argument to be passed to smartctl.

If you want to guess at the configuration, call it with -g and it will print out what it thinks
it should be.

5. Restart snmpd on your host

If you have a large number of more than one or two disks on a system,
you should consider adding this to cron. Also make sure the cache file
is some place it can be written to.

```
 */3 * * * * /etc/snmp/smart -u
```

6. If your snmp agent runs as user "snmp", edit your sudo users
   (usually `visudo`) and add at the bottom:

```
snmp ALL=(ALL) NOPASSWD: /etc/snmp/smart, /usr/bin/env smartctl

```

and modify your snmpd.conf file accordingly:

```
extend smart /usr/bin/sudo /etc/snmp/smart
```

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

If you set useSN to 1, it is worth noting that you will loose
history(not able to access it from the web interface) for that device
each time you change it. You will also need to run camcontrol or the
like on said server to figure out what device actually corresponds
with that serial number.

Also if the system you are using uses non-static device naming based
on bus information, it may be worthwhile just using the SN as the
device ID is going to be irrelevant in that case.

# Squid

## SNMP Proxy

1: Enable SNMP for Squid like below, if you have not already, and
restart it.

```
acl snmppublic snmp_community public
snmp_port 3401
snmp_access allow snmppublic localhost
snmp_access deny all
```

2: Restart squid on your host.

3: Edit your snmpd.conf file and add, making sure you have the same
community, host, and port as above:

```
proxy -v 2c -Cc -c public 127.0.0.1:3401 1.3.6.1.4.1.3495
```

For more advanced information on Squid and SNMP or setting up proxying
for net-snmp, please see the links below.

<http://wiki.squid-cache.org/Features/Snmp>
<http://www.net-snmp.org/wiki/index.php/Snmpd_proxy>

# TinyDNS aka djbdns

## Agent

[Install the agent](Agent-Setup.md) on this device if it isn't already
and copy the `tinydns` script to `/usr/lib/check_mk_agent/local/`

_Note_: We assume that you use DJB's
[Daemontools](http://cr.yp.to/daemontools.html) to start/stop
tinydns. And that your tinydns instance is located in `/service/dns`,
adjust this path if necessary.

1: Replace your _log_'s `run` file, typically located in
   `/service/dns/log/run` with:

```bash
#!/bin/sh
exec setuidgid dnslog tinystats ./main/tinystats/ multilog t n3 s250000 ./main/
```

2: Create tinystats directory and chown:

```bash
mkdir /service/dns/log/main/tinystats
chown dnslog:nofiles /service/dns/log/main/tinystats
```

3: Restart TinyDNS and Daemontools: `/etc/init.d/svscan restart`
   _Note_: Some say `svc -t /service/dns` is enough, on my install
   (Gentoo) it doesn't rehook the logging and I'm forced to restart it
   entirely.

# Unbound

Unbound configuration:

```text
# Enable extended statistics.
server:
        extended-statistics: yes
        statistics-cumulative: yes

remote-control:
        control-enable: yes
        control-interface: 127.0.0.1

```

Restart your unbound after changing the configuration, verify it is
working by running `unbound-control stats`.

## Option 1: SNMP Extend (Preferred and easiest method)

1: Copy the shell script, unbound, to the desired host. `wget
https://github.com/librenms/librenms-agent/raw/master/snmp/unbound -O
/etc/snmp/unbound`

2: Run `chmod +x /etc/snmp/unbound`

3: Edit your snmpd.conf file and add:

```
extend unbound /usr/bin/sudo /etc/snmp/unbound
```

4: Restart snmpd.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

## Option 2: Agent

[Install the agent](#agent-setup) on this device if it isn't already
and copy the `unbound.sh` script to `/usr/lib/check_mk_agent/local/`

# UPS-nut

A small shell script that exports nut ups status.

## SNMP Extend

1: Copy the [ups
   nut](https://github.com/librenms/librenms-agent/blob/master/snmp/ups-nut.sh)
   to `/etc/snmp/` on your host.

2: Make the script executable (chmod +x /etc/snmp/ups-nut.sh)

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend ups-nut /etc/snmp/ups-nut.sh
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# UPS-apcups

A small shell script that exports apcacess ups status.

## SNMP Extend

1: Copy the [ups
   apcups](https://github.com/librenms/librenms-agent/blob/master/snmp/ups-apcups)
   to `/etc/snmp/` on your host.

2: Run `chmod +x /etc/snmp/ups-apcups`

3: Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend ups-apcups /etc/snmp/ups-apcups
```

If 'apcaccess' is not in the PATH enviromental variable snmpd is
using, you may need to do something like below.

```
extend ups-apcups/usr/bin/env PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin /etc/snmp/ups-apcups
```

4: Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

# Voip-monitor

Shell script that reports cpu-load/memory/open-files files stats of Voip Monitor

## SNMP Extend

1: Download the script onto the desired host. `wget
   https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/voipmon-stats.sh
   -O /etc/snmp/voipmon-stats.sh`

2: Make the script executable: `chmod +x /etc/snmp/voipmon-stats.sh`

3: Edit your snmpd.conf file (usually `/etc/snmp/voipmon-stats.sh`) and add:

```
extend voipmon /etc/snmp/voipmon-stats.sh
```

# ZFS

## SNMP Extend

`zfs-linux` requires python3 >=python3.5.

The installation steps are:

1. Copy the polling script to the desired host (the host must be added
   to LibreNMS devices)
1. Make the script executable
1. Edit snmpd.conf to include ZFS stats

### FreeBSD

```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/zfs-freebsd -O /etc/snmp/zfs-freebsd
chmod +x /etc/snmp/zfs-freebsd
echo "extend zfs /etc/snmp/zfs-freebsd" >> /etc/snmp/snmpd.conf
```

### Linux

```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/zfs-linux -O /etc/snmp/zfs-linux
chmod +x /etc/snmp/zfs-linux
echo "extend zfs sudo /etc/snmp/zfs-linux" >> /etc/snmp/snmpd.conf
```

Edit your sudo users (usually `visudo`) and add at the bottom:

```
snmp ALL=(ALL) NOPASSWD: /etc/snmp/zfs-linux
```


Now restart snmpd and you're all set.
