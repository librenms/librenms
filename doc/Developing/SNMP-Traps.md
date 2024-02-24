# Creating snmp trap handlers

You must have a working snmptrapd. See
[SNMP TRAP HANDLER](../Extensions/SNMP-Trap-Handler.md)

Make sure the MIB is loaded from the trap you are adding. Edit
`/etc/systemd/system/snmptrapd.service.d/mibs.conf` to add it then
restart snmptrapd.

`MIBDIRS` option is not recursive, so you need to specify each directory individually.

Create a new class in `LibreNMS\Snmptrap\Handlers` that implements the
`LibreNMS\Interfaces\SnmptrapHandler` interface. For example:

```php
<?php
/**
 * ColdBoot.php
 *
 * Handles the SNMPv2-MIB::coldStart trap
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class ColdBoot implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param Device $device
     * @param Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $trap->log('SNMP Trap: Device ' . $device->displayName() . ' cold booted', $device->device_id, 'reboot', Severity::Warning);
    }
}

```

where number on the end means color of the eventlog:

```
Severity::Ok = green
Severity::Info = cyan
Severity::Notice = blue
Severity::Warning = yellow
Severity::Error = red
```

Register the mapping in the `config/snmptraps.php` file. Make sure to
use the full trap OID and correct class.

```php
'SNMPv2-MIB::coldStart' => \LibreNMS\Snmptrap\Handlers\ColdBoot::class,
```

The handle function inside your new class will receive a LibreNMS/Snmptrap/Trap
object containing the parsed trap.  It is common to update the database and create
event log entries within the handle function.

### Getting information from the Trap

#### Source information

```php
$trap->getDevice();   // gets Device model for the device associated with this trap
$trap->ip;            // gets source IP of this trap
$trap->getTrapOid();  // returns the string you registered your class with
```

#### Retrieving data from the Trap

```php
$trap->getOidData('IF-MIB::ifDescr.114');
```

getOidData() requires the full name including any additional index.
You can use these functions to search the OID keys.

```php
$trap->findOid('ifDescr');  // returns the first oid key that contains the string
$trap->findOids('ifDescr'); // returns all oid keys containing the string
```

#### Advanced

If the above isn't adequate, you can get the entire trap text:

```php
$trap->raw;
```

### Tests

Submitting new traps requires them to be fully tested. You can find many examples in the
`tests/Feature/SnmpTraps/` directory.

Here is a basic example of a test that trap handler only creates a log message.
If your trap modifies the database, you should also test that it does so.

```php
<?php

namespace LibreNMS\Tests\Feature\SnmpTraps;

class ColdStratTest extends SnmpTrapTestCase
{
    public function testColdStart(): void
    {
        $this->assertTrapLogsMessage(rawTrap: <<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:1:12.7
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::coldStart
TRAP,
            log: 'SNMP Trap: Device {{ hostname }} cold booted', // The log message sent
            failureMessage: 'Failed to handle SNMPv2-MIB::coldStart', // an informative message to let user know what failed
            args: [4, 'reboot'], // the additional arguments to the log method
        );
    }
}
```
