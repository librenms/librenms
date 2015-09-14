This document will explain how to add full support for a new OS. **A minimal knowledge in programming in PHP is needed.**

#### MIB

At first you need to copy the MIB file to the right directory. Look at inside the file to find the OID you want to use.

```bash
/opt/librenms/mibs //for example
```

Here you can test and OID with the snmpget command:

```bash
snmpget -v2c -c public -OUsb -m PULSESECURE-PSG-MIB -M /opt/librenms/mibs -t 30 secure iveCpuUtil.0
//quick explanation -m MIBFILE -M MIB directory hostname OID

iveCpuUtil.0 = Gauge32: 28
```

#### New OS definition

You then need to declare the new OS in the definition file located here:

```bash
includes/definitions.inc.php
```

```php
// Pulse Secure OS definition
$os = 'pulse';
$config['os'][$os]['text']             = 'Pulse Secure';
$config['os'][$os]['type']             = 'firewall';
$config['os'][$os]['icon']             = 'junos';
$config['os'][$os]['over'][0]['graph'] = 'device_bits';
$config['os'][$os]['over'][0]['text']  = 'Device Traffic';
$config['os'][$os]['over'][1]['graph'] = 'device_processor';
$config['os'][$os]['over'][1]['text']  = 'CPU Usage';
$config['os'][$os]['over'][2]['graph'] = 'device_mempool';
$config['os'][$os]['over'][2]['text']  = 'Memory Usage';


//Don't forget to declare the specific graphs if needed. It will be located near the end of the file.

//Pulse Secure Graphs
$config['graph_types']['device']['pulse_users']['section']         = 'firewall';
$config['graph_types']['device']['pulse_users']['order']           = '0';
$config['graph_types']['device']['pulse_users']['descr']           = 'Active Users';
$config['graph_types']['device']['pulse_sessions']['section']      = 'firewall';
$config['graph_types']['device']['pulse_sessions']['order']        = '0';
$config['graph_types']['device']['pulse_sessions']['descr']        = 'Active Sessions';
```

#### Discovery OS

You need to create a new file named as you OS definition and in this directory:

```bash
includes/discovery/os/pulse.inc.php
```

You can check other files to get help in the code. For the example, it can be like this :

```php
// Pulse Secure OS definition
<?php
if (!$os) {
    if (strstr($sysDescr, 'Pulse Connect Secure')) {
        $os = 'pulse';
    }
}
```

As we declare Memory and CPU graphs, we need to declare the right OID in a PHP file in the following directories:


**Memory**

```bash
includes/discovery/mempools/pulse.inc.php
```

```php
<?php
//
// Hardcoded discovery of Memory usage on Pulse Secure devices.
//
if ($device['os'] == 'pulse') {
    echo 'PULSE-MEMORY-POOL: ';

    $usage = str_replace('"', "", snmp_get($device, 'PULSESECURE-PSG-MIB::iveMemoryUtil.0', '-OvQ'));

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'pulse-mem', 'Main Memory', '100', null, null);
    }
}
```

**CPU**

```bash
includes/discovery/processors/pulse.inc.php
```

```php
<?php
//
// Hardcoded discovery of CPU usage on Pulse Secure devices.
//
if ($device['os'] == 'pulse') {
    echo 'Pulse Secure : ';

    $descr = 'Processor';
    $usage = str_replace('"', "", snmp_get($device, 'PULSESECURE-PSG-MIB::iveCpuUtil.0', '-OvQ'));

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, 'PULSESECURE-PSG-MIB::iveCpuUtil.0', '0', 'pulse-cpu', $descr,
 '100', $usage, null, null);
    }
}
```


#### Polling OS

We wil now do the same for the polling process for retrieving the informations.

**Memory**

```bash
includes/polling/mempools/pulse-mem.inc.php
```

```php
<?php

// Simple hard-coded poller for Pulse Secure
// Yes, it really can be this simple.

echo 'Pulse Secure MemPool'.'\n';

if ($device['os'] == 'pulse') {
  $perc     = str_replace('"', "", snmp_get($device, "PULSESECURE-PSG-MIB::iveMemoryUtil.0", '-OvQ'));
  $memory_available = str_replace('"', "", snmp_get($device, "UCD-SNMP-MIB::memTotalReal.0", '-OvQ'));
  $mempool['total'] = $memory_available;

  if (is_numeric($perc)) {
    $mempool['used'] = ($memory_available / 100 * $perc);
    $mempool['free'] = ($memory_available - $mempool['used']);
  }

  echo "PERC " .$perc."%\n";
  echo "Avail " .$mempool['total']."\n";

}
```


**CPU**

```bash
includes/polling/processors/pulse-cpu.inc.php
```

```php
<?php
// Simple hard-coded poller for Pulse Secure
// Yes, it really can be this simple.
echo 'Pulse Secure CPU Usage';

if ($device['os'] == 'pulse') {
    $usage = str_replace('"', "", snmp_get($device, 'PULSESECURE-PSG-MIB::iveCpuUtil.0', '-OvQ'));

    if (is_numeric($usage)) {
        $proc = ($usage * 100);
    }
}
```


Here are the specific graphs based on the OID in the vendor MIB:

```bash
includes/polling/os/pulse.inc.php
```
For exemple, here we declare two specific graphs for users and sessions numbers. Theses two graphs will be display on the firewall section of the graphs tab.

```php
<?php

$version = trim(snmp_get($device, "productVersion.0", "-OQv", "PULSESECURE-PSG-MIB"),'"');
$hardware = "Juniper " . trim(snmp_get($device, "productName.0", "-OQv", "PULSESECURE-PSG-MIB"),'"');
$hostname = trim(snmp_get($device, "sysName.0", "-OQv", "SNMPv2-MIB"),'"');

$usersrrd  = $config['rrd_dir'].'/'.$device['hostname'].'/pulse_users.rrd';
$users = snmp_get($device, 'PULSESECURE-PSG-MIB::iveConcurrentUsers.0', '-OQv');

if (is_numeric($users)) {
    if (!is_file($usersrrd)) {
        rrdtool_create($usersrrd, ' DS:users:GAUGE:600:0:U'.$config['rrd_rra']);
    }
    rrdtool_update($usersrrd, "N:$users");
    $graphs['pulse_users'] = true;
}

$sessrrd  = $config['rrd_dir'].'/'.$device['hostname'].'/pulse_sessions.rrd';
$sessions = snmp_get($device, 'PULSESECURE-PSG-MIB::iveConcurrentUsers.0', '-OQv');

if (is_numeric($sessions)) {
    if (!is_file($sessrrd)) {
        rrdtool_create($sessrrd, ' DS:sessions:GAUGE:600:0:U '.$config['rrd_rra']);
    }
    rrdtool_update($sessrrd, "N:$sessions");
    $graphs['pulse_sessions'] = true;
}
```
We finish in the declaration of the two graph types in the Data Base:

You can do that within a file if you wish to share your work and contribute in the developpement of LibreNMS. :-)

```bash
sql-schema/xxx.sql

php includes/sql-schema/update.php
```

Or do the SQL commands directly in Mysql or PhpMyadmin:

```php
INSERT INTO `graph_types`(`graph_type`, `graph_subtype`, `graph_section`, `graph_descr`, `graph_order`) VALUES ('device',  'pulse_users',  'firewall',  'Active Users',  '');
INSERT INTO `graph_types`(`graph_type`, `graph_subtype`, `graph_section`, `graph_descr`, `graph_order`) VALUES ('device',  'pulse_sessions',  'firewall',  'Active Sessions',  '');
```

#### Displaying

To the specific graphs, we need to write the PHP code in this directory:

**Pulse Sessions**

```bash
html/includes/graphs/device/pulse_sessions.inc.php
```

```php
<?php

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('pulse_sessions.rrd');

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

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('pulse_users.rrd');

require 'includes/graphs/common.inc.php';

$ds = 'users';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Users';

require 'includes/graphs/generic_simplex.inc.php';
```


#### The final part : the tests

Test of the discovery process
```bash
./discovery.php -h hostname
```

Test of the polling process
```bash
./poller.php -h hostname
```

At this step we should see all the right information in LibreNMS.
