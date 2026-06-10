# Asterisk

A small shell script that reports various Asterisk call status.

### SNMP Extend

1. Download the [asterisk
script](https://github.com/librenms/librenms-agent/blob/master/snmp/asterisk)
to `/etc/snmp/` on your asterisk server.

```bash
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/asterisk -O /etc/snmp/asterisk
```

2. Make the script executable

```bash
chmod +x /etc/snmp/asterisk
```

3. Configure `ASCLI` in the script.

4. Verify it is working by running `/etc/snmp/asterisk`

5. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```bash
extend asterisk /etc/snmp/asterisk
```

6. Restart snmpd on your host



The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.