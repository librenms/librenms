## C.H.I.P

C.H.I.P. is a $9 R8 based tiny computer ideal for small projects.
Further details: <https://getchip.com/pages/chip>

1. Copy the shell script to the host.

```bash
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/chip.sh -O /etc/snmp/power-stat.sh
```

2. Make the script executable.

```bash
chmod +x /etc/snmp/power-stat.sh
```

3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

```bash
extend power-stat /etc/snmp/power-stat.sh
```

4. Restart snmpd on your host.

LibreNMS discovers the application automatically, as described at the
top of the page. If the discovery fails, do the steps under the `SNMP
Extend` heading at the top of the page.