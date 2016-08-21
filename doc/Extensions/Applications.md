Applications
------------
You can use Application support to graph performance statistics from many applications.

Different applications support a variety of ways collect data: by direct connection to the application, snmpd extend, or the agent.

1. [BIND9/named](#bind9-aka-named) - Agent
2. [MySQL](#mysql) - Agent
3. [NGINX](#nginx) - Agent
4. [NTPD](#ntpd-server) - extend SNMP, Agent
5. [PowerDNS](#powerdns) - Agent
6. [PowerDNS Recursor](#powerdns-recursor) - Agent
7. [TinyDNS/djbdns](#tinydns-aka-djbdns) - Agent
8. [OS Updates](#os-updates) - extend SNMP
9. [DHCP Stats](#dhcp-stats) - extend SNMP
10. [Memcached](#memcached) - extend SNMP
11. [Unbound](#unbound) - Agent
12. [Proxmox](#proxmos) - extend SNMP
13. [Raspberry PI](#raspberry-pi) - extend SNMP
14. [Agent Setup](#agent-setup)

### BIND9 aka named

##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `bind` script to `/usr/lib/check_mk_agent/local/`

Create stats file with appropriate permissions:
```shell
~$ touch /etc/bind/named.stats
~$ chown bind:bind /etc/bind/named.stats
```
Change `user:group` to the user and group that's running bind/named.

Bind/named configuration:
```text
options {
	...
	statistics-file "/etc/bind/named.stats";
	zone-statistics yes;
	...
};
```
Restart your bind9/named after changing the configuration.

Verify that everything works by executing `rndc stats && cat /etc/bind/named.stats`.
In case you get a `Permission Denied` error, make sure you chown'ed correctly.

Note: if you change the path you will need to change the path in `scripts/agent-local/bind`.


### MySQL

##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `mysql` script to `/usr/lib/check_mk_agent/local/`

The MySQL script requires PHP-CLI and the PHP MySQL extension, so please verify those are installed.

Unlike most other scripts, the MySQL script requires a configuration file `/usr/lib/check_mk_agent/local/mysql.cnf` with following content:

```php
<?php
$mysql_user = 'root';
$mysql_pass = 'toor';
$mysql_host = 'localhost';
$mysql_port = 3306;
```

Verify it is working by running `/usr/lib/check_mk_agent/local/mysql`

### NGINX

NGINX is a free, open-source, high-performance HTTP server: https://www.nginx.org/

##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `nginx` script to `/usr/lib/check_mk_agent/local/`

It's required to have the following directive in your nginx configuration responsible for the localhost server:

```text
location /nginx-status {
    stub_status on;
    access_log   off;
    allow 127.0.0.1;
    deny all;
}
```

### NTPD Server
Supports NTPD Server (not client, that is separate)

##### Extend SNMP
1. Download the script onto the desired host (the host must be added to LibreNMS devices)
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/ntpd-server.php -o /etc/snmp/ntpd-server.php
```
2. Make the script executable (chmod +x /etc/snmp/ntdp-server.php)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend ntpdserver /etc/snmp/ntpd-server.php
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `Ntpd-server` under the Applications tab.

##### Agent
Support is built into the agent, and this app will be automatically enabled.

### PowerDNS
An authoritative DNS server: https://www.powerdns.com/auth.html

##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `powerdns` script to `/usr/lib/check_mk_agent/local/`


### PowerDNS Recursor
A recursive DNS server: https://www.powerdns.com/recursor.html

##### Direct
The LibreNMS polling host must be able to connect to port 8082 on the monitored device.
The web-server must be enabled, see the Recursor docs: https://doc.powerdns.com/md/recursor/settings/#webserver

###### Variables
`$config['apps']['powerdns-recursor']['api-key']` required, this is defined in the Recursor config
`$config['apps']['powerdns-recursor']['port']` numeric, defines the port to connect to PowerDNS Recursor on.  The default is 8082
`$config['apps']['powerdns-recursor']['https']` true or false, defaults to use http.

##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `powerdns-recursor` script to `/usr/lib/check_mk_agent/local/`

This script uses `rec_control get-all` to collect stats.


### TinyDNS aka  djbdns

##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `tinydns` script to `/usr/lib/check_mk_agent/local/`

_Note_: We assume that you use DJB's [Daemontools](http://cr.yp.to/daemontools.html) to start/stop tinydns.
And that your tinydns instance is located in `/service/dns`, adjust this path if necessary.

1. Replace your _log_'s `run` file, typically located in `/service/dns/log/run` with:
```shell
#!/bin/sh
exec setuidgid dnslog tinystats ./main/tinystats/ multilog t n3 s250000 ./main/
```
2. Create tinystats directory and chown:
```shell
mkdir /service/dns/log/main/tinystats
chown dnslog:nofiles /service/dns/log/main/tinystats
```
3. Restart TinyDNS and Daemontools: `/etc/init.d/svscan restart`
   _Note_: Some say `svc -t /service/dns` is enough, on my install (Gentoo) it doesn't rehook the logging and I'm forced to restart it entirely.

### OS Updates
A small shell script that checks your system package manager for any available updates. Supports apt-get/pacman/yum/zypper package managers).

For pacman users automatically refreshing the database, it is recommended you use an alternative database location `--dbpath=/var/lib/pacman/checkupdate`

##### Extend SNMP
1. Copy the shell script to the desired host (the host must be added to LibreNMS devices)
2. Make the script executable (chmod +x /opt/os-updates.sh)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend osupdate /opt/os-updates.sh
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `OS Updates` under the Applications tab.

_Note_: apt-get depends on an updated package index. There are several ways to have your system run `apt-get update` automatically. The easiest is to create `/etc/apt/apt.conf.d/10periodic` and pasting the following in it: `APT::Periodic::Update-Package-Lists "1";`.
If you have apticron, cron-apt or apt-listchanges installed and configured, chances are that packages are already updated periodically.

### DHCP Stats
A small shell script that reports current DHCP leases stats.

##### Extend SNMP
1. Copy the shell script to the desired host (the host must be added to LibreNMS devices)
2. Make the script executable (chmod +x /opt/dhcp-status.sh)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend dhcpstats /opt/dhcp-status.sh
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `DHCP Stats` under the Applications tab.

### Memcached
1. Copy the [memcached script](https://github.com/librenms/librenms-agent/blob/master/agent-local/memcached) to `/usr/local/bin` (or any other suitable location) on your remote server.
2. Make the script executable: `chmod +x /usr/local/memcached`
3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
```
extend memcached /usr/local/bin/memcached
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check `Memcached` under the Applications tab.

### Unbound

##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `unbound.sh` script to `/usr/lib/check_mk_agent/local/`

Unbound configuration:

```text
# Enable extended statistics.
server:
        statistics-interval: 0
        extended-statistics: yes
        statistics-cumulative: yes
```

Restart your unbound after changing the configuration,v erify it is working by running /usr/lib/check_mk_agent/local/unbound.sh

### Proxmox
1. Download the script onto the desired host (the host must be added to LibreNMS devices)
`wget https://github.com/librenms/librenms-agent/blob/master/agent-local/proxmox -o /usr/local/bin/proxmox`
2. Make the script executable: `chmod +x /usr/local/proxmox`
3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
`extend proxmox /usr/local/bin/proxmox`
(Note: if your snmpd doesn't run as root, you might have to invoke the script using sudo. `extend proxmox /usr/bin/sudo /usr/local/bin/proxmox`)
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check `Proxmox` on the Applications tab.

=======
### Raspberry PI
SNMP extend script to get your PI data into your host.

##### Extend SNMP
1. Copy the [raspberry script](https://github.com/librenms/librenms-agent/blob/master/snmp/raspberry.sh) to `/opt/` (or any other suitable location) on your PI host.
2. Make the script executable: `chmod +x /opt/raspberry.sh`
3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
```
extend raspberry /opt/raspberry.sh
```
4. Edit your sudo users (usually `visudo`) and add at the bottom:
```
snmp ALL=(ALL) NOPASSWD: /opt/raspberry.sh, /usr/bin/vcgencmd*
```
5. Restart snmpd on PI host


Agent Setup
-----------

To gather data from remote systems you can use LibreNMS in combination with check_mk (found [here](https://github.com/librenms/librenms-agent)).

Make sure that systemd or xinetd is installed on the host you want to run the agent on.

The agent uses TCP-Port 6556, please allow access from the **LibreNMS host** and **poller nodes** if you're using the [Distributed Polling](http://docs.librenms.org/Extensions/Distributed-Poller/) setup.

On each of the hosts you would like to use the agent on then you need to do the following:

1. Clone the `librenms-agent` repository:

```shell
cd /opt/
git clone https://github.com/librenms/librenms-agent.git
cd librenms-agent
```

2. Copy the relevant check_mk_agent to `/usr/bin`:

| linux | freebsd |
| --- | --- |
| `cp check_mk_agent /usr/bin/check_mk_agent` | `cp check_mk_agent_freebsd /usr/bin/check_mk_agent` |

```shell
chmod +x /usr/bin/check_mk_agent
```

3. Copy the service file(s) into place.

| xinetd | systemd |
| --- | --- |
| `cp check_mk_xinetd /etc/xinetd.d/check_mk` | `cp check_mk@.service check_mk.socket /etc/systemd/system` |

4. Create the relevant directories.

```shell
mkdir -p /usr/lib/check_mk_agent/plugins /usr/lib/check_mk_agent/local
```

5. Copy each of the scripts from `agent-local/` into `/usr/lib/check_mk_agent/local` that you require to be graphed.  You can find detail setup instructions for specific applications above.
6. Make each one executable that you want to use with `chmod +x /usr/lib/check_mk_agent/local/$script`
7. Enable the check_mk service

| xinetd | systemd |
| --- | --- |
| `/etc/init.d/xinetd restart` | `systemctl enable check_mk.socket && systemctl start check_mk.socket` |

8. Login to the LibreNMS web interface and edit the device you want to monitor. Under the modules section, ensure that unix-agent is enabled.
9. Then under Applications, enable the apps that you plan to monitor.
10. Wait for around 10 minutes and you should start seeing data in your graphs under Apps for the device.
