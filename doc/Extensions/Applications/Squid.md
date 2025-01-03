
## Squid

### SNMP Proxy

1. Enable SNMP for Squid like below, if you have not already, and restart it.

```bash
acl snmppublic snmp_community public
snmp_port 3401
snmp_access allow snmppublic localhost
snmp_access deny all
```

2. Restart squid on your host.

3. Edit your `/etc/snmp/snmpd.conf` file and add, making sure you have the same community, host, and port as above:

```bash
proxy -v 2c -Cc -c public 127.0.0.1:3401 1.3.6.1.4.1.3495
```

For more advanced information on Squid and SNMP or setting up proxying
for net-snmp, please see the links below.

<http://wiki.squid-cache.org/Features/Snmp>
<http://www.net-snmp.org/wiki/index.php/Snmpd_proxy>
