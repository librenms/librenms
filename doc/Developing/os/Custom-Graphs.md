# Custom Device Graphs

To add a custom device graph, define where it appears, collect its data, and
create a graph class. This page covers the definition and polling steps. See
[Creating Graphs](../Graphs.md) for the class-based graph method and a complete
`PulseUsersGraph` example that displays the data collected below.

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

To customize these settings locally, use `lnms config:set`.

```bash
lnms config:set graph_types.device.pulse_users '{"section":"firewall","order":0,"descr":"Active Users"}'
lnms config:set graph_types.device.pulse_sessions '{"section":"firewall","order":0,"descr":"Active Sessions"}'
```

These settings define the graphs' labels and sections. The poller enables each
graph for an individual device by calling `enableGraph()` after collecting
valid data.

## Polling OS

OS polling is not always the correct place for your own polling code.
For guidance, ask a core developer on
[Discord](https://t.libren.ms/discord).

Update the example file with more polling:

```bash
LibreNMS/OS/Pulse.php
```

The example below collects the number of users and enables `pulse_users` for
this device. The graph settings above place it in the firewall section of the
Graphs tab. Add equivalent polling for `pulse_sessions` to show sessions too.

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

## Displaying

Create `app/Graphs/Device/PulseUsersGraph.php` using the example in
[Creating Graphs](../Graphs.md#2-create-the-graph-class). It reads the
`pulse_users` measurement and its `users` field, matching the poller above.

The graph appears in the firewall section of the device's Graphs tab after
polling collects data and enables the graph. Use the same steps for
`pulse_sessions`, with a matching poller and graph class.
