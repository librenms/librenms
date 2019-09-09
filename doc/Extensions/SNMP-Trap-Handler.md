source: Extensions/SNMP-Trap-Handler.md
path: blob/master/doc/

# SNMP trap handling

Currently, librenms only supports linkUp/linkDown (port up/down),
bgpEstablished/bgpBackwardTransition (BGP Sessions Up/Down) and
authenticationFailure SNMP traps. To add more see [Adding new SNMP Trap handlers](../Developing/SNMP-Traps.md)

Traps are handled via snmptrapd.

## Configure snmptrapd

Install snmptrapd via your package manager.

To enable snmptrapd to properly parse traps, we will need to add MIBs.

Make the folder `/etc/systemd/system/snmptrapd.service.d/` and edit
the file `/etc/systemd/system/snmptrapd.service.d/mibs.conf` and add
the following content. You may want to tweak to add vendor directories
for devices you care about (in addition to or instead of cisco).

```ini
[Service]
Environment=MIBDIRS=+/opt/librenms/mibs:/opt/librenms/mibs/cisco
Environment=MIBS=+ALL
```

For non-systemd systems, you can edit TRAPDOPTS in the init script in /etc/init.d/snmptrapd.

`TRAPDOPTS="-Lsd  -M /opt/librenms/mibs -m ALL -f -p $TRAPD_PID"`

In `/etc/snmp/snmptrapd.conf`, add something like the following:

```text
traphandle default /opt/librenms/snmptrap.php
```

Along with any necessary configuration to receive the traps from your
devices (community, etc.)

Reload service files, enable, and start the snmptrapd service:

```
sudo systemctl daemon-reload
sudo systemctl enable snmptrapd
sudo systemctl restart snmptrapd
```

### Event logging

You can configure generic event logging for snmp traps.  This will log
an event of the type trap for received traps. These events can be utilized for alerting.

In config.php

```php
$config['snmptraps']['eventlog'] = 'unhandled';
```

Valid options are:

- `unhandled` only unhandled traps will be logged
- `all` log all traps
- `none` no traps will create a generic event log (handled traps may still log events)
