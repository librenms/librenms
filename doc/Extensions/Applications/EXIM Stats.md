## EXIM Stats

SNMP extend script to get your exim stats data into your host.

### SNMP Extend

1. Download the script onto the desired host.

```bash
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/exim-stats.sh -O /etc/snmp/exim-stats.sh
```

2. Make the script executable

```bash
chmod +x /etc/snmp/exim-stats.sh
```

3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

```bash
extend exim-stats /etc/snmp/exim-stats.sh
```

4. If you are using sudo edit your sudo users (usually `visudo`) and
add at the bottom:

```bash
snmp ALL=(ALL) NOPASSWD: /etc/snmp/exim-stats.sh, /usr/bin/exim*
```

5. Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.