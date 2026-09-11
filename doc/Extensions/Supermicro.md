# Supermicro

An agent is necessary for some Supermicro information in LibreNMS.

## Supermicro SuperDoctor
Install Supermicro SuperDoctor on the device to monitor.

Then add this line to `/etc/snmp/snmpd.conf`:

```bash
pass .1.3.6.1.4.1.10876 /usr/bin/sudo /opt/Supermicro/SuperDoctor5/libs/native/snmpagent
```

Restart net-snmp:

```bash
service snmpd restart
```
