This document will explain how to add basic and full support for a new OS. **Some knowledge in PHP is needed for the full support.**


#### BASIC SUPPORT FOR A NEW OS

### MIB

If we have the MIB, we can copy the file into the default directory:

```bash
/opt/librenms/mibs
```

### New OS definition
Let's begin to declare the new OS in LibreNMS. At first we modify the definition file located here:

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

//The icon described before is the image we have to create and put in the directory html/images/os
```

### Discovery OS

We create a new file named as our OS definition and in this directory:

```bash
includes/discovery/os/pulse.inc.php
```
This file just sets the $os variable, done by checking the SNMP tree for a particular value that matches the OS you are adding. Typically, this will come from the presence of specific values in sysObjectID or sysDescr, or the existence of a particular enterprise tree.
Look at other files to get help in the code structure.

```php
<?php

if (!$os) {
    if (strstr($sysDescr, 'Pulse Connect Secure')) {
        $os = 'pulse';
    }
}
```

Here is the file location for polling the new OS within a vendor MIB or a standard one:

```bash
includes/polling/os/pulse.inc.php
```
This file will usually set the variables for $version, $hardware and $hostname retrieved from an snmp lookup.

```php
<?php

$version = trim(snmp_get($device, "productVersion.0", "-OQv", "PULSESECURE-PSG-MIB"),'"');
$hardware = "Juniper " . trim(snmp_get($device, "productName.0", "-OQv", "PULSESECURE-PSG-MIB"),'"');
$hostname = trim(snmp_get($device, "sysName.0", "-OQv", "SNMPv2-MIB"),'"');
```

Quick explanation and examples : 

```bash
snmpwalk -v2c -c public -m SNMPv2-MIB -M mibs
//will give the overall OIDs that can be retrieve with this standard MIB. OID on the left side and the result on the right side
//Then we have just to pick the wanted OID and do a check

snmpget -v2c -c public -OUsb -m SNMPv2-MIB -M /opt/librenms/mibs -t 30 HOSTNAME SNMPv2-SMI::mib-2.1.1.0
//sysDescr.0 = STRING: Juniper Networks,Inc,Pulse Connect Secure,VA-DTE,8.1R1 (build 33493)

snmpget -v2c -c public -OUsb -m SNMPv2-MIB -M /opt/librenms/mibs -t 30 HOSTNAME SNMPv2-SMI::mib-2.1.5.0
//sysName.0 = STRING: pulse-secure

//Here the same with the vendor MIB and the specific OID
snmpget -v2c -c public -OUsb -m PULSESECURE-PSG-MIB -M /opt/librenms_old/mibs -t 30 HOSTNAME productName.0
//productName.0 = STRING: "Pulse Connect Secure,VA-DTE"

snmpget -v2c -c public -OUsb -m PULSESECURE-PSG-MIB -M /opt/librenms/mibs -t 30 HOSTNAME productVersion.0
//productVersion.0 = STRING: "8.1R1 (build 33493)"
```

#### The final check

Discovery
```bash
./discovery.php -h HOSTNAME
```

Polling
```bash
./poller.php -h HOSTNAME
```

At this step we should see all the values retrieved in LibreNMS.



#### FULL SUPPORT FOR A NEW OS

### MIB

At first we copy the MIB file into the default directory:

```bash
/opt/librenms/mibs
```

We are now ready to look at inside the file and find the OID we want to use. _For this documentation we'll use Pulse Secure devices._

Then we can test it with the snmpget command (hostname must be reachable):

```bash
//for example the OID iveCpuUtil.0:
snmpget -v2c -c public -OUsb -m PULSESECURE-PSG-MIB -M /opt/librenms/mibs -t 30 HOSTNAME iveCpuUtil.0
//quick explanation : snmpget -v2c -c COMMUNITY -OUsb -m MIBFILE -M MIB DIRECTORY HOSTNAME OID

//Result here:
iveCpuUtil.0 = Gauge32: 28
```

### New OS definition
Let's begin to declare the new OS in LibreNMS. At first we modify the definition file located here:

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

//The icon described before is the image we have to create and put in the directory html/images/os

//Don't forget to declare the specific graphs if needed. It will be located near the end of the file.

//Pulse Secure Graphs
$config['graph_types']['device']['pulse_users']['section']         = 'firewall';
$config['graph_types']['device']['pulse_users']['order']           = '0';
$config['graph_types']['device']['pulse_users']['descr']           = 'Active Users';
$config['graph_types']['device']['pulse_sessions']['section']      = 'firewall';
$config['graph_types']['device']['pulse_sessions']['order']        = '0';
$config['graph_types']['device']['pulse_sessions']['descr']        = 'Active Sessions';
```

### Discovery OS

We create a new file named as our OS definition and in this directory:

```bash
includes/discovery/os/pulse.inc.php
```

Look at other files to get help in the code structure. For this example, it can be like this :

```php
// Pulse Secure OS definition
<?php
if (!$os) {
    if (strstr($sysDescr, 'Pulse Connect Secure')) {
        $os = 'pulse';
    }
}
```

As we declared Memory and CPU graphs before, we declare the OID in a PHP file :


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

_Please keep in mind that the PHP code is often different for the needs of the devices and the information we retrieve._

#### Polling OS

We will now do the same for the polling process:

**Memory**

```bash
includes/polling/mempools/pulse-mem.inc.php
```

```php
<?php

// Simple hard-coded poller for Pulse Secure
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
echo 'Pulse Secure CPU Usage';

if ($device['os'] == 'pulse') {
    $usage = str_replace('"', "", snmp_get($device, 'PULSESECURE-PSG-MIB::iveCpuUtil.0', '-OvQ'));

    if (is_numeric($usage)) {
        $proc = ($usage * 100);
    }
}
```

Here is the file location for the specific graphs based on the OID in the vendor MIB:

```bash
includes/polling/os/pulse.inc.php
```
We declare two specific graphs for users and sessions numbers. Theses two graphs will be displayed on the firewall section of the graphs tab as it was written in the definition include file.

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
We finish in the declaration of the two graph types in the database:

We can do that within a file to share our work and contribute in the development of LibreNMS. :-)

```bash
sql-schema/xxx.sql
//check the file number in GitHub

php includes/sql-schema/update.php
```

Or put the SQL commands directly in Mysql or PhpMyadmin for our tests:

```php
INSERT INTO `graph_types`(`graph_type`, `graph_subtype`, `graph_section`, `graph_descr`, `graph_order`) VALUES ('device',  'pulse_users',  'firewall',  'Active Users',  '');
INSERT INTO `graph_types`(`graph_type`, `graph_subtype`, `graph_section`, `graph_descr`, `graph_order`) VALUES ('device',  'pulse_sessions',  'firewall',  'Active Sessions',  '');
```

#### Displaying

The specific graphs are not displayed automatically so we need to write the following PHP code:

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


#### The final check

Discovery
```bash
./discovery.php -h HOSTNAME
```

Polling
```bash
./poller.php -h HOSTNAME
```

At this step we should see all the values retrieved in LibreNMS.
