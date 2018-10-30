source: Extensions/SNMP-Trap-Handler.md
path: blob/master/doc/
# SNMP trap handling

Currently, librenms only supports linkUp/linkDown (port up/down), bgpEstablished/bgpBackwardTransition (BGP Sessions Up/Down) and authenticationFailure SNMP traps.
To add more see [Adding new SNMP Trap handlers](../Developing/SNMP-Traps.md)

Traps are handled via snmptrapd.

## Configure snmptrapd

Install snmptrapd via your package manager.

Modify startup options to include `-M /opt/librenms/mibs -m ALL`

In `/etc/snmp/snmptrapd.conf`, add something like the following:

```text
traphandle default /opt/librenms/snmptrap.php
```

Along with any necessary configuration to receive the traps from your devices (community, etc.)

### Event logging

You can configure generic event logging for snmp traps.  This will log an event of the type trap for received traps.
These events can be utilized for alerting.

In config.php
```php
$config['snmptraps']['eventlog'] = 'unhandled';
```

Valid options are:
 - `unhandled` only unhandled traps will be logged
 - `all` log all traps
 - `none` no traps will create a generic event log (handled traps may still log events)
