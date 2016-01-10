Agent setup
-----------

To gather data from remote systems you can use LibreNMS in combination with check_mk (included in the scripts directory).

Make sure that xinetd is installed on the host you want to run the agent on.

The agent uses TCP-Port 6556, please allow access from the LibreNMS-Host and Poller-Nodes if you're using the Distributed Polling setup.

On each of the hosts you would like to use the agent on then you need to do the following:

* Clone the `librenms-agent` repository:

```shell
cd /opt/
git clone https://github.com/librenms/librenms-agent.git
cd librenms-agent
cp check_mk_agent /usr/bin/check_mk_agent
chmod +x /usr/bin/check_mk_agent
```

* Copy the xinetd config file into place.

```shell
cp check_mk_xinetd /etc/xinetd.d/check_mk
```

* Create the relevant directories.

```shell
mkdir -p /usr/lib/check_mk_agent/plugins /usr/lib/check_mk_agent/local
```

* Copy each of the scripts from `agent-local/` into `/usr/lib/check_mk_agent/local` that you require to be graphed.
* Make each one executable that you want to use with `chmod +x /usr/lib/check_mk_agent/local/$script`
* And restart xinetd.

```shell
/etc/init.d/xinetd restart
```

* Login to the LibreNMS web interface and edit the device you want to monitor. Under the modules section, ensure that unix-agent is enabled.
* Then under Applications, enable the apps that you plan to monitor.
* Wait, in around 10 minutes you should start seeing data in your graphs under Apps for the device.

## Application Specific Configuration

### BIND9/named

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

### TinyDNS/djbdns

__Installation__:

1. Get tinystats sources from http://www.morettoni.net/tinystats.en.html
2. Compile like as advised.  
  _Note_: In case you get `Makefile:9: *** missing separator.  Stop.`, compile manually using:  
    * With IPv6: `gcc -Wall -O2 -fstack-protector -DWITH_IPV6 -o tinystats tinystats.c`  
    * Without IPv6: `gcc -Wall -O2 -fstack-protector -o tinystats tinystats.c`  
3. Install into preferred path, like `/usr/bin/`.

__Configuration__:

_Note_: In this part we assume that you use DJB's [Daemontools](http://cr.yp.to/daemontools.html) to start/stop tinydns.  
And that your tinydns-instance is located in `/service/dns`, adjust this path if necessary.

1. Replace your _log_'s `run` file, typically located in `/service/dns/log/run` with:  
  ```
  #!/bin/sh
  
  exec setuidgid dnslog tinystats ./main/tinystats/ multilog t n3 s250000 ./main/
  ```
2. Create tinystats directory and chown:  
  `mkdir /service/dns/log/main/tinystats && chown dnslog:nofiles /service/dns/log/main/tinystats`
3. Restart TinyDNS and Daemontools: `/etc/init.d/svscan restart`  
   _Note_: Some say `svc -t /service/dns` is enough, on my install (Gentoo) it doesn't rehook the logging and I'm forced to restart it entirely.

### MySQL

Unlike most other scripts, the MySQL script requires a configuration file `/usr/lib/check_mk_agent/local/mysql.cnf` with following content:

```php
<?php
$mysql_user = 'root';
$mysql_pass = 'toor';
$mysql_host = 'localhost';
$mysql_port = 3306;
```

NOTE: This only applies to the PHP-Version of the Statistics-poller. There's work being done in porting this script to Python, a different configuration file (if any) will apply.

### Nginx

It's required to have the following directive in your nginx-configuration responsible for the localhost-server:

```text
location /nginx-status {
    stub_status on;
    access_log   off;
    allow 127.0.0.1;
    deny all;
}
```

