
# SDFS info

A small shell script that exportfs SDFS volume info.

## SNMP Extend

1. Download the script onto the desired host

```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/sdfsinfo -O /etc/snmp/sdfsinfo
```

2. Make the script executable

```
chmod +x /etc/snmp/sdfsinfo
```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```
extend sdfsinfo /etc/snmp/sdfsinfo
```

4. Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.
