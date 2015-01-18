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
