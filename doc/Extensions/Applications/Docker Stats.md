## Docker Stats

It gathers metrics about the docker containers, including:
- cpu percentage
- memory usage
- container size
- uptime
- Totals per status

This script requires python3 and the pip module python-dateutil

### SNMP Extend

1. Install pip module

```bash
pip3 install python-dateutil
```

2. Copy the shell script to the desired host.
By default, it will only show the status for containers that are running. To include all containers modify the constant in the script at the top of the file and change it to `ONLY_RUNNING_CONTAINERS = False`

```bash
wget https://github.com/librenms/librenms-agent/raw/master/snmp/docker-stats.py -O /etc/snmp/docker-stats.py
```

3. Make the script executable

```bash
chmod +x /etc/snmp/docker-stats.py
```

4. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```bash
extend docker /etc/snmp/docker-stats.py
```

5. If your run Debian, you need to add the Debian-snmp user to the docker group

```bash
usermod -a -G docker Debian-snmp
```

6. Restart snmpd on your host

```bash
systemctl restart snmpd
```