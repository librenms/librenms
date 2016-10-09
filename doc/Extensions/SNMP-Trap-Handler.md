source: Extensions/SNMP-Trap-Handler.md
# SNMP trap handling

Currently, librenms only supports port up/down SNMP traps.  Traps are handled via snmptrapd.

## Configure snmptrapd

Install snmptrapd via your package manager.

In `/etc/snmp/snmptrapd.conf`, add something like the following:

```text
traphandle default /opt/librenms/snmptrap.php
```

Along with any necessary configuration to receive the traps from your devices (community, etc.)
