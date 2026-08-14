## BIRD2

The BIRD Internet Routing Daemon (BGP)

The BIRD daemon has no SNMP support. This application therefore reads
all the configured BGP protocols and sends them to LibreNMS.
This application supports both IPv4 and IPv6 Peer processing.

### SNMP Extend

1. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

```bash
extend bird2 '/usr/bin/sudo /usr/sbin/birdc -r show protocols all'
```

2.  Edit your sudo users (usually `visudo`) and add at the bottom:

```bash
Debian-snmp ALL=(ALL) NOPASSWD: /usr/sbin/birdc
```

_If your SNMP daemon runs as a user other than `Debian-snmp`, give that
user permission to run `birdc`._

3. Verify the time format for bird2 is defined. Otherwise `iso short
   ms` (hh:mm:ss) is the default value. The datetime parsing logic of the
   bird show command does not accept this format. The bird2 app parsing
   logic needs `timeformat protocol`.

Example starting point using Bird2 shorthand `iso long` (YYYY-MM-DD hh:mm:ss):

```bash
timeformat base iso long;
timeformat log iso long;
timeformat protocol iso long;
timeformat route iso long;
```

*Timezone can be manually specified, example "%F %T %z" (YYYY-MM-DD
hh:mm:ss +11:45). See the [Bird
2 docs](https://bird.network.cz/?get_doc&v=20&f=bird-3.html) for more information*

4. Restart snmpd on your host.

LibreNMS discovers the application automatically, as described at the
top of the page. If the discovery fails, do the steps under the `SNMP
Extend` heading at the top of the page.