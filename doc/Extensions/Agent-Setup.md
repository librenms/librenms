Agent setup
-----------

To gather data from remote systems you can use LibreNMS in combination with check_mk (included in the scripts directory).

On each of the hosts you would like to use the agent on then you need to do the following:

* Copy the `check_mk_agent` script into `/usr/bin` and make it executable.

```shell
cp scripts/check_mk_agent /usr/bin/check_mk_agent
chmod +x /usr/bin/check_mk_agent
```

* Copy the xinetd config file into place.

```shell
cp scripts/check_mk_xinetd /etc/xinetd.d/check_mk
```

* Create the relevant directories.

```shell
mkdir -p /usr/lib/check_mk_agent/plugins /usr/lib/check_mk_agent/local
```

* Copy each of the scripts from `scripts/agent-local/` into `/usr/lib/check_mk_agent/local`
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

Verify that everything works by executing `rdnc stats && cat /etc/bind/named.stats`.  
In case you get a `Permission Denied` error, make sure you chown'ed correctly.

Note: if you change the path you will need to change the path in `scripts/agent-local/bind`.

### TinyDNS/djbdns

__Installation__:

1. Get tinystats sources from http://www.morettoni.net/tinystats.en.html
2. Compile like as advised.  
  _Note_: In case you get `Makefile:9: *** missing separator.  Stop.`, compile manually using:  
    * With IPv6: `gcc -Wall -O2 -fstack-protector -DWITH_IPV6 -o tinystats tinystats.c`  
    * Without IPv6: `gcc -Wall -O2 -fstack-protector -o tinystats tinystats.c`  
3. Install into prefered path, like `/usr/bin/`.

__Configuration__:

_Note_: In this part we assume that you use DJB's [Daemontools](http://cr.yp.to/daemontools.html) to start/stop tinydns.  
And that your tinydns-instance is located in `/service/dns`, adjust this path if necesary.

1. Replace your _log_'s `run` file, typically located in `/service/dns/log/run` with:  
  ```
  #!/bin/sh
  
  exec setuidgid dnslog tinystats ./main/tinystats/ multilog t n3 s250000 ./main/
  ```
2. Create tinystats directory and chown:  
  `mkdir /service/dns/log/main/tinystats && chown dnslog:nofiles /service/dns/log/main/tinystats`
3. Restart TinyDNS and Daemontools: `/etc/init.d/svscan restart`  
   _Note_: Some say `svc -t /service/dns` is enough, on my install (Gentoo) it doesnt rehook the logging and I'm forced to restart it entirely.

