First define your graphs in
`resources/definitions/config_definitions.json`. Your work is then
available to everyone.

```json
        "graph_types.device.pulse_users": {
            "default": {
                "section": "firewall",
                "order": 0,
                "descr": "Active Users"
            },
            "type": "graph"
        },
        "graph_types.device.pulse_sessions": {
            "default": {
                "section": "firewall",
                "order": 0,
                "descr": "Active Sessions"
            },
            "type": "graph"
        },
```

Without a contribution, put the definitions in `config.php`.

```config.php
// Pulse Secure Graphs
$config['graph_types']['device']['pulse_users'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Active Users'];
$config['graph_types']['device']['pulse_sessions'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Active Sessions'];
```

#### Polling OS

OS polling is not always the correct place for your own polling code.
For guidance, ask a core developer on
[Discord](https://t.libren.ms/discord).

Update the example file with more polling:

```bash
LibreNMS/OS/Pulse.php
```

This code declares two graphs: one for the number of users and one for
the number of sessions. The two graphs appear in the firewall section
of the graphs tab. The definition include file sets this position.

```php
<?php

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class Pulse extends \LibreNMS\OS implements OSPolling
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        $users = SnmpQuery::get('PULSESECURE-PSG-MIB::iveConcurrentUsers.0')->value();

        if (is_numeric($users)) {
            $rrd_def = RrdDefinition::make()->addDataset('users', 'GAUGE', 0);

            $fields = [
                'users' => $users,
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'pulse_users', $tags, $fields);
            $this->enableGraph('pulse_users');
        }
    }
}
```

#### Displaying

LibreNMS does not show these graphs automatically. Add this PHP code:

**Pulse Users**

```bash
includes/html/graphs/device/pulse_users.inc.php
```

```php
<?php

$rrd_filename = Rrd::name($device['hostname'], 'pulse_users');

require 'includes/html/graphs/common.inc.php';

$ds = 'users';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Users';

require 'includes/html/graphs/generic_simplex.inc.php';
```

The graph appears in the web interface after the first data
collection.
