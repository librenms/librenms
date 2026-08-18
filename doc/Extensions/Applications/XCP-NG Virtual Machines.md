
# XCP-NG Virtual Machines

!!! note
    This application needs the vminfo discovery module and the vminfo
    polling module. LibreNMS does NOT detect it under applications.
    You also need the distro script in `snmpd.conf`. The example [Linux
    snmpd config](../../Support/SNMP-Configuration-Examples.md#linux-snmpd-v2)
    describes it.

!!! note
    Only SNMP is supported.

## SNMP Pass Persist

1: Fetch the script in question and make it executable.

```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/xcp-ng-vminfo -O /etc/snmp/xcp-ng-vminfo
    chmod +x /etc/snmp/xcp-ng-vminfo
```

3: Add this line to `/etc/snmp/snmpd.conf` and restart snmpd.

```
    pass_persist .1.3.6.1.4.1.60652.100 /bin/bash /etc/snmp/xcp-ng-vminfo
```

```bash
    systemctl restart snmpd
```
