source: Developing/os/Custom-Graphs.md

If you are adding custom graphs, please add the following to `includes/definitions.inc.php`:
```php
//Don't forget to declare the specific graphs if needed. It will be located near the end of the file.

//Pulse Secure Graphs
$config['graph_types']['device']['pulse_users']['section']         = 'firewall';
$config['graph_types']['device']['pulse_users']['order']           = '0';
$config['graph_types']['device']['pulse_users']['descr']           = 'Active Users';
$config['graph_types']['device']['pulse_sessions']['section']      = 'firewall';
$config['graph_types']['device']['pulse_sessions']['order']        = '0';
$config['graph_types']['device']['pulse_sessions']['descr']        = 'Active Sessions';
```

#### Polling OS

OS polling is not necessarily where custom polling should be done, please speak to one of the core devs in irc for guidance.

Let's update our example file to add additional polling:

```bash
includes/polling/os/pulse.inc.php
```
We declare two specific graphs for users and sessions numbers. Theses two graphs will be displayed on the firewall section of the graphs tab as it was written in the definition include file.

```php
<?php

use LibreNMS\RRD\RrdDefinition;

$users = snmp_get($device, 'iveConcurrentUsers.0', '-OQv', 'PULSESECURE-PSG-MIB');

if (is_numeric($users)) {
    $rrd_def = RrdDefinition::make()->addDataset('users', 'GAUGE', 0);

    $fields = array(
        'users' => $users,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pulse_users', $tags, $fields);
    $graphs['pulse_users'] = true;
}

$sessions = snmp_get($device, 'iveConcurrentUsers.0', '-OQv', 'PULSESECURE-PSG-MIB');

if (is_numeric($sessions)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0);

    $fields = array(
        'sessions' => $sessions,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pulse_sessions', $tags, $fields);
    $graphs['pulse_sessions'] = true;
}
```
We finish in the declaration of the two graph types in the database:

We can do that within a file to share our work and contribute in the development of LibreNMS. :-)

```bash
sql-schema/xxx.sql
php includes/sql-schema/update.php
./scripts/build-schema.php
```

Or put the SQL commands directly in Mysql or PhpMyadmin for our tests:

```php
INSERT INTO `graph_types`(`graph_type`, `graph_subtype`, `graph_section`, `graph_descr`, `graph_order`) VALUES ('device',  'pulse_users',  'firewall',  'Active Users',  0);
INSERT INTO `graph_types`(`graph_type`, `graph_subtype`, `graph_section`, `graph_descr`, `graph_order`) VALUES ('device',  'pulse_sessions',  'firewall',  'Active Sessions',  0);
```

#### Displaying

The specific graphs are not displayed automatically so we need to write the following PHP code:

**Pulse Sessions**

```bash
html/includes/graphs/device/pulse_sessions.inc.php
```

```php
<?php

$rrd_filename = rrd_name($device['hostname'], 'pulse_sessions');

require 'includes/graphs/common.inc.php';

$ds = 'sessions';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'Sessions';

require 'includes/graphs/generic_simplex.inc.php';
```

**Pulse Users**

```bash
html/includes/graphs/device/pulse_users.inc.php
```

```php
<?php

$rrd_filename = rrd_name($device['hostname'], 'pulse_users');

require 'includes/graphs/common.inc.php';

$ds = 'users';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Users';

require 'includes/graphs/generic_simplex.inc.php';
```

That should be it, after data has started to be collected graphs should appear in the WebUI.
