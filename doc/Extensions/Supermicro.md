source: Extensions/Supermicro.md
path: blob/master/doc/

# Introduction
For some Supermicro information to show up in LibreNMS, you will need to install an agent.

# Supermicro SuperDoctor
Install Supermicro SuperDoctor onto the device you want to monitor.

Then add the following to /etc/snmp/snmpd.conf:

```bash
pass .1.3.6.1.4.1.10876 /usr/bin/sudo /opt/Supermicro/SuperDoctor5/libs/native/snmpagent
```

Restart net-snmp:

```bash
service snmpd restart
```