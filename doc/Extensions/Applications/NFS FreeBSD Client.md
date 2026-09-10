## FreeBSD NFS Client

The [generalized NFS support](NFS.md) replaces this application.

### SNMP Extend

1. Copy the shell script, fbsdnfsserver, to the desired host

```bash
wget https://github.com/librenms/librenms-agent/raw/master/snmp/fbsdnfsclient -O /etc/snmp/fbsdnfsclient
```

2. Make the script executable.

```bash
chmod +x /etc/snmp/fbsdnfsclient
```

3. Edit your `snmpd.conf` file and add:

```bash
extend fbsdnfsclient /etc/snmp/fbsdnfsclient
```

4. Restart snmpd on your host.

LibreNMS discovers the application automatically, as described at the
top of the page. If the discovery fails, do the steps under the `SNMP
Extend` heading at the top of the page.