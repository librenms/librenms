## BIRD2

The BIRD Internet Routing Daemon (BGP)

Due to the lack of SNMP support in the BIRD daemon, this application extracts all configured BGP protocols and parses it into LibreNMS.
This application supports both IPv4 and IPv6 Peer processing.

### SNMP Extend

1. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```bash
extend bird2 '/usr/bin/sudo /usr/sbin/birdc -r show protocols all'
```

2.  Edit your sudo users (usually `visudo`) and add at the bottom:

```bash
Debian-snmp ALL=(ALL) NOPASSWD: /usr/sbin/birdc
```

_If your snmp daemon is running on a user that isnt `Debian-snmp` make sure that user has the correct permission to execute `birdc`_

3. Verify the time format for bird2 is defined. Otherwise `iso short
   ms` (hh:mm:ss) is the default value that will be used. Which is not
   compatible with the datetime parsing logic used to parse the output
   from the bird show command. `timeformat protocol` is the one
   important to be defibned for the bird2 app parsing logic to work.

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

4. Restart snmpd on your host

The application should be auto-discovered as described at the top of the page. If it is not, please follow the steps set out under `SNMP Extend` heading top of page.