source: Developing/SNMP-Traps.md
path: blob/master/doc/

# Creating snmp trap handlers

Create a new class in LibreNMS\Snmptrap\Handlers that implements the
LibreNMS\Interfaces\SnmptrapHandler interface. 

Register the mapping in the config/snmptraps.php file. Make sure to use the full trap oid.

```php
'IF-MIB::linkUp' => \LibreNMS\Snmptrap\Handlers\LinkUp::class
```

The handle function inside your new class will receive a LibreNMS/Snmptrap/Trap
object containing the parsed trap.  It is common to update the database and create
event log entries within the handle function.


### Getting information from the Trap

#### Source information

```php
$trap->getDevice();   // gets Device model for the device associated with this trap
$trap->getHostname(); // gets hostname sent with the trap
$trap->getIp();       // gets source IP of this trap
$trap->getTrapOid();  // returns the string you registered your class with
```

#### Retrieving data from the Trap

```php
$trap->getOidData('IF-MIB::ifDescr.114');
```

getOidData() requires the full name including any additional index.
You can use these functions to search the oid keys.

```php
$trap->findOid('ifDescr');  // returns the first oid key that contains the string
$trap->findOids('ifDescr'); // returns all oid keys containing the string
```

#### Advanced

If the above isn't adequate, you can get the entire trap text

```php
$trap->getRaw();
```
