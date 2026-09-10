
## Squid

### SNMP Proxy

1. Enable SNMP for Squid as below. Then restart Squid.

```bash
acl snmppublic snmp_community public
snmp_port 3401
snmp_access allow snmppublic localhost
snmp_access deny all
```

2. Restart squid on your host.

3. Edit your `/etc/snmp/snmpd.conf` file and add this line. Use the
same community, host, and port as above:

```bash
proxy -v 2c -Cc -c public 127.0.0.1:3401 1.3.6.1.4.1.3495
```

For more information about Squid with SNMP, and about a proxy setup for
net-snmp, read the links below.

<http://wiki.squid-cache.org/Features/Snmp>
<http://www.net-snmp.org/wiki/index.php/Snmpd_proxy>
