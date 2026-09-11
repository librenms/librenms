## Entropy

A small shell script that checks your system's available random entropy.

### SNMP Extend

1. Download the script onto the host.

```bash
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/entropy.sh -O /etc/snmp/entropy.sh
```

2. Make the script executable.

```bash
chmod +x /etc/snmp/entropy.sh
```

3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

```bash
extend entropy /etc/snmp/entropy.sh
```

4. Restart snmpd on your host.

LibreNMS discovers the application automatically, as described at the
top of the page. If the discovery fails, do the steps under the `SNMP
Extend` heading at the top of the page.