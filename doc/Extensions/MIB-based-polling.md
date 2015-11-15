## WARNING ##

MIB-based polling is experimental.  It might overload your LibreNMS server,
destroy your data, set your routers on fire, and kick your cat.  It has been
tested against a very limited set of devices (namely Ruckus ZD1000 wireless
controllers, and `net-snmp` on Linux).  It may fail badly on other hardware.

The approach taken is fairly basic and I claim no special expertise in
understanding MIBs.  Most of my understanding of SNMP comes from reading
net-snmp man pages, and reverse engineering the output of snmptranslate and
snmpwalk and trying to make devices work with LibreNMS.  I may have made
false assumptions and probably use wrong terminology in many places.  Feel
free to offer corrections/suggestions via pull requests or email.

Paul Gear <paul@librenms.org>

## Overview ##

MIB-based polling is disabled by default; you must set
    `$config['poller_modules']['mib'] = 1;`
in `config.php` to enable it.

The components involved in of MIB-based support are:

### Discovery ###

  - MIB-based detection is not involved; any work done here would have to be
    duplicated by the poller and thus would only increase load.

### Polling ###

  - The file `includes/snmp.inc.php` now contains code which can parse MIBs
    using `snmptranslate` and use the data returned to populate an array
    which guides the poller in what to store.  At the moment, only OIDs with
    Unsigned32 and Counter64 data types are parsed.
  - `includes/polling/mib.inc.php` looks for a MIB matching sysObjectID in
    the MIB directory; if one is found, it:
    - parses it
    - walks that MIB on the device
    - stores any numeric results in individual RRD files
    - updates/adds graph definitions in the previously-unused graph_types
      database table
  - Individual OSes (`includes/polling/os/*.inc.php`) can poll extra MIBs
    for a given OS by calling `poll_mib()`.  At the moment, this actually
    happens before the general MIB polling.
  - Devices may be excluded from MIB polling by changing the setting in the
    device edit screen (`/device/device=ID/tab=edit/section=modules/`)

### Graphing ###

  - For each graph type defined in the database, a graph will appear in:
	Device -> Graphs -> MIB
  - MIB graphs are generated generically by
    `html/includes/graphs/device/mib.inc.php`
  - At the moment, all units are placed in the same graph.  This is probably
    non-optimal for, e.g., wifi controllers with hundreds of APs attached.

## Adding/testing other device types ##

One of the goals of this work is to help take out the heavy lifting of
adding new device types.  Even if you want fully customised graphs or
tables, you can use the automatic collection of MIBs to make it easy to
gather the data you want.

### How to add a new device MIB ###

 1. Ensure the manufacturer's MIB is present in the mibs directory.  If you
    plan to submit your work to LibreNMS, make sure you attribute the source
    of the MIB, including the exact download URL.
 2. Check that `snmptranslate -Ts -M mibs -m MODULE | grep mibName` produces
    a named list of OIDs.  See the comments for `snmp_mib_walk()` in
    `includes/snmp.inc.php` for an example.
 3. Check that `snmptranslate -Td -On -M mibs -m MODULE MODULE::mibName`
    produces a parsed description of the OID values.  An example can be
    found in the comments for `snmp_mib_parse()` in `includes/snmp.inc.php`.
 4. Get the `sysObjectID` from a device, for example:
 ```snmpget -v2c -c public -OUsb -m SNMPv2-MIB -M /opt/librenms/mibs -t 30 hostname sysObjectID.0```
 5. Ensure `snmptranslate -m all -M /opt/librenms/mibs OID 2>/dev/null`
    (where OID is the value returned for sysObjectID above) results in a
    valid name for the MIB.  See the comments for `snmp_translate()` in
    `includes/snmp.inc.php` for an example.  If this step fails, it means
    there is something wrong with the MIB and `net-snmp` cannot parse it.
 6. Add any additional MIBs you wish to poll for specific device types to
    `includes/polling/os/OSNAME.inc.php` by calling `poll_mibs()` with the
    MIB module and name.  See `includes/polling/os/ruckuswireless.inc.php` for
    an example.
 7. That should be all you need to see MIB graphs!

## TODO ##

  - Save the most recent MIB data in the database (including string types
    which cannot be graphed).  Display it in the appropriate places.
  - Parse and save integer and timetick data types.
  - Filter MIBs/OIDs from being polled and/or saved.
  - Move graphs from the MIB section to elsewhere. e.g. There is already
    specific support for wireless APs - this should be utilised, but isn't
    yet.
  - Combine multiple MIB values into graphs automatically on a predefined or
    user-defined basis.
  - Include MIB types in stats submissions.
