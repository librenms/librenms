## PowerDNS-dnsdist

### SNMP Extend

1. Copy the BASH script to the desired host.
```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/powerdns-dnsdist -O /etc/snmp/powerdns-dnsdist
```

2. Make the script executable
```
chmod +x /etc/snmp/powerdns-dnsdist
```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
```
extend powerdns-dnsdist /etc/snmp/powerdns-dnsdist
```

4. Restart snmpd on your host.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.