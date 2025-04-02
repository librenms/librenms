First we define our graphs in `misc/config_definitions.json` to share
our work and contribute in the development of LibreNMS. :-)

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

Alternatively, place in `config.php` if you don't plan to contribute.

```config.php
// Pulse Secure Graphs
$config['graph_types']['device']['pulse_users'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Active Users'];
$config['graph_types']['device']['pulse_sessions'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Active Sessions'];
```

#### Polling OS

OS polling is not necessarily where custom polling should be done,
please speak to one of the core devs in
[Discord](https://t.libren.ms/discord) for guidance.

Let's update our example file to add additional polling:

```bash
librenms/LibreNMS/OS/Pulse.php
```

We declare two specific graphs for users and sessions numbers. Theses
two graphs will be displayed on the firewall section of the graphs tab
as it was written in the definition include file.

```php
<?php

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;

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

The specific graphs are not displayed automatically so we need to
write the following PHP code:

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

**Pulse Sessions**

```bash
includes/html/graphs/device/pulse_sessions.inc.php
```

```php
<?php

$rrd_filename = Rrd::name($device['hostname'], 'pulse_sessions');

require 'includes/html/graphs/common.inc.php';

$ds = 'sessions';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Sessions';

require 'includes/graphs/generic_simplex.inc.php';
```

That should be it, after data has started to be collected graphs
should appear in the WebUI.
