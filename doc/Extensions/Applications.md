source: Extensions/Applications.md
Applications
------------
You can use Application support to graph performance statistics from many applications.

Different applications support a variety of ways collect data: by direct connection to the application, snmpd extend, or the agent.

1. [Agent Setup](#agent-setup)
1. [Apache](#apache) - SNMP extend
1. [BIND9/named](#bind9-aka-named) - Agent
1. [DHCP Stats](#dhcp-stats) - SNMP extend
1. [GPSD](#gpsd) - Agent
1. [Mailscanner](#mailscanner) - SNMP extend
1. [Memcached](#memcached) - SNMP extend
1. [MySQL](#mysql) - Agent
1. [NGINX](#nginx) - Agent
1. [NTP Client](#ntp-client) - SNMP extend
1. [NTP Server](#ntp-server) - SNMP extend
1. [OS Updates](#os-updates) - SNMP extend
1. [PowerDNS](#powerdns) - Agent
1. [PowerDNS Recursor](#powerdns-recursor) - Direct, Agent
1. [Proxmox](#proxmox) - SNMP extend
1. [Raspberry PI](#raspberry-pi) - SNMP extend
1. [TinyDNS/djbdns](#tinydns-aka-djbdns) - Agent
1. [Unbound](#unbound) - Agent
1. [UPS-nut](#ups-nut) - SNMP extend
1. [UPS-apcups](#ups-apcups) - SNMP extend
1. [EXIM Stats](#exim-stats) - SNMP extend
1. [Munin](#munin) - Agent

### Apache
Either use SNMP extend or use the agent.
##### SNMP Extend
1. Download the script onto the desired host (the host must be added to LibreNMS devices)
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/apache-stats.py -O /etc/snmp/apache-stats.py
```
2. Make the script executable (chmod +x /etc/snmp/apache-stats.py)
3. Verify it is working by running /etc/snmp/apache-stats.py
(In some cases urlgrabber needs to be installed, in Debian it can be achieved by: apt-get install python-urlgrabber)
4. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend apache /etc/snmp/apache-stats.py
```
5. Restart snmpd on your host

##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `apache` script to `/usr/lib/check_mk_agent/local/`

1. Verify it is working by running /usr/lib/check_mk_agent/local/apache
(If you get error like "Can't locate LWP/Simple.pm". libwww-perl needs to be installed: apt-get install libwww-perl)
2. On the device page in Librenms, edit your host and check the `Apache` under the Applications tab.


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



### DHCP Stats
A small shell script that reports current DHCP leases stats.

##### SNMP Extend
1. Copy the shell script to the desired host (the host must be added to LibreNMS devices)
2. Make the script executable (chmod +x /etc/snmp/dhcp-status.sh)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend dhcpstats /etc/snmp/dhcp-status.sh
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `DHCP Stats` under the Applications tab.



### GSPD
A small shell script that reports GPSD status.

##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `gpsd` script to `/usr/lib/check_mk_agent/local/`

You may need to configure `$server` or `$port`.

Verify it is working by running `/usr/lib/check_mk_agent/local/gpsd`



### Mailscanner
##### SNMP Extend
1. Download the script onto the desired host (the host must be added to LibreNMS devices)
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mailscanner.php -O /etc/snmp/mailscanner.php
```
2. Make the script executable (chmod +x /etc/snmp/mailscanner.php)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend mailscanner /etc/snmp/mailscanner.php
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `Mailscanner` under the Applications tab.



### Memcached
##### SNMP Extend
1. Copy the [memcached script](https://github.com/librenms/librenms-agent/blob/master/agent-local/memcached) to `/etc/snmp/` on your remote server.
2. Make the script executable: `chmod +x /etc/snmp/memcached`
3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
```
extend memcached /etc/snmp/memcached
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check `Memcached` under the Applications tab.



### MySQL
##### Agent
[Install the agent](#agent-setup) on this device if it isn't already and copy the `mysql` script to `/usr/lib/check_mk_agent/local/`

The MySQL script requires PHP-CLI and the PHP MySQL extension, so please verify those are installed.

CentOS
```
yum install php-cli php-mysql
```

Debian
```
apt-get install php5-cli php5-mysql
```

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


### NTP Client
A shell script that gets stats from ntp client.

##### SNMP Extend
1. Download the script onto the desired host (the host must be added to LibreNMS devices)
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/ntp-client.sh -O /etc/snmp/ntp-client.sh
```
2. Make the script executable (chmod +x /etc/snmp/ntp-client.sh)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend ntp-client /etc/snmp/ntp-client.sh
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `NTP Client` under the Applications tab.



### NTP Server (NTPD)
A shell script that gets stats from ntp server (ntpd).

##### SNMP Extend
1. Download the script onto the desired host (the host must be added to LibreNMS devices)
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/ntp-server.sh -O /etc/snmp/ntp-server.sh
```
2. Make the script executable (chmod +x /etc/snmp/ntp-server.sh)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend ntp-server /etc/snmp/ntp-server.sh
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `NTP Server` under the Applications tab.



### OS Updates
A small shell script that checks your system package manager for any available updates. Supports apt-get/pacman/yum/zypper package managers).

For pacman users automatically refreshing the database, it is recommended you use an alternative database location `--dbpath=/var/lib/pacman/checkupdate`

##### SNMP Extend
1. Download the script onto the desired host (the host must be added to LibreNMS devices)
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/os-updates.sh -O /etc/snmp/os-updates.sh
```
2. Make the script executable (chmod +x /etc/snmp/os-updates.sh)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend osupdate /etc/snmp/os-updates.sh
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `OS Updates` under the Applications tab.

_Note_: apt-get depends on an updated package index. There are several ways to have your system run `apt-get update` automatically. The easiest is to create `/etc/apt/apt.conf.d/10periodic` and pasting the following in it: `APT::Periodic::Update-Package-Lists "1";`.
If you have apticron, cron-apt or apt-listchanges installed and configured, chances are that packages are already updated periodically.



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



### Proxmox
1. Download the script onto the desired host (the host must be added to LibreNMS devices)
`wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/proxmox -O /usr/local/bin/proxmox`
2. Make the script executable: `chmod +x /usr/local/bin/proxmox`
3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
`extend proxmox /usr/local/bin/proxmox`
(Note: if your snmpd doesn't run as root, you might have to invoke the script using sudo. `extend proxmox /usr/bin/sudo /usr/local/bin/proxmox`)
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check `Proxmox` on the Applications tab.



### Raspberry PI
SNMP extend script to get your PI data into your host.

##### SNMP Extend
1. Copy the [raspberry script](https://github.com/librenms/librenms-agent/blob/master/snmp/raspberry.sh) to `/etc/snmp/` (or any other suitable location) on your PI host.
2. Make the script executable: `chmod +x /etc/snmp/raspberry.sh`
3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
```
extend raspberry /etc/snmp/raspberry.sh
```
4. Edit your sudo users (usually `visudo`) and add at the bottom:
```
snmp ALL=(ALL) NOPASSWD: /etc/snmp/raspberry.sh, /usr/bin/vcgencmd*
```
5. Restart snmpd on PI host



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



### UPS-nut
A small shell script that exports nut ups status.

##### SNMP Extend
1. Copy the [ups nut](https://github.com/librenms/librenms-agent/blob/master/snmp/ups-nut.sh) to `/etc/snmp/` on your host.
2. Make the script executable (chmod +x /etc/snmp/ups-nut.sh)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend ups-nut /etc/snmp/ups-nut.sh
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `UPS nut` under the Applications tab.



### UPS-apcups
A small shell script that exports apcacess ups status.

##### SNMP Extend
1. Copy the [ups apcups](https://github.com/librenms/librenms-agent/blob/master/snmp/ups-apcups.sh) to `/etc/snmp/` on your host.
2. Make the script executable (chmod +x /etc/snmp/ups-apcups.sh)
3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend ups-apcups /etc/snmp/ups-apcups.sh
```
4. Restart snmpd on your host
5. On the device page in Librenms, edit your host and check the `UPS apcups` under the Applications tab.

### EXIM Stats
SNMP extend script to get your exim stats data into your host.

##### SNMP Extend
1. Copy the [exim stats](https://github.com/librenms/librenms-agent/blob/master/snmp/exim-stats.sh) to `/etc/snmp/` (or any other suitable location) on your host.
2. Make the script executable: `chmod +x /etc/snmp/exim-stats.sh`
3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
```
extend exim-stats /etc/snmp/exim-stats.sh
```
4. If you are using sudo edit your sudo users (usually `visudo`) and add at the bottom:
```
snmp ALL=(ALL) NOPASSWD: /etc/snmp/exim-stats.sh, /usr/bin/exim*
```
5. Restart snmpd on your host

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

### Munin

#### Agent
1. Install the script to your agent: `wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/munin -O /usr/lib/check_mk_agent/local/munin`
2. Make the script executable (`chmod +x /usr/lib/check_mk_agent/local/munin`)
3. Create the munin scripts dir: `mkdir -p /usr/share/munin`
4. Install your munin scripts into the above directory.

To create your own custom munin scripts, please see this example:

```
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
