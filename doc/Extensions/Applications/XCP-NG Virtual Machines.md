
# XCP-NG Virtual Machines

!!! note
    This requires the vminfo discovery and polling module to be enabled, it is NOT detected under applications.
    You also need to have the distro script setup in snmpd.conf as detailed in the example [Linux snmpd config](../../Support/SNMP-Configuration-Examples.md#linux-snmpd-v2)

!!! note
    Only snmp is supported.

## SNMP Pass Persist

1: Fetch the script in question and make it executable.

```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/xcp-ng-vminfo -O /etc/snmp/xcp-ng-vminfo
    chmod +x /etc/snmp/xcp-ng-vminfo
```

3: Add the following to `/etc/snmp/snmpd.conf` and restart snmpd.

```
    pass_persist .1.3.6.1.4.1.60652.100 /bin/bash /etc/snmp/xcp-ng-vminfo
```

```bash
    systemctl restart snmpd
```
