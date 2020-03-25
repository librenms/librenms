<?php
namespace LibreNMS;

use LibreNMS\Config;

class Discovery
{
    
    public function __construct()
    {
        $init_modules = array('discovery');
        include_once base_path() . '/includes/init.php';
    }

    public function os($device)
    {
        $device['sysDescr']    = \LibreNMS\SNMP::get($device, "SNMPv2-MIB::sysDescr.0", "-Ovq");
        $device['sysObjectID'] = \LibreNMS\SNMP::get($device, "SNMPv2-MIB::sysObjectID.0", "-Ovqn");

        d_echo("| {$device['sysDescr']} | {$device['sysObjectID']} | \n");

        $deferred_os = array(
            'freebsd',
            'linux',
        );

        // check yaml files
        $os_defs = Config::get('os');
        foreach ($os_defs as $os => $def) {
            if (isset($def['discovery']) && !in_array($os, $deferred_os)) {
                foreach ($def['discovery'] as $item) {
                    if ($this->check($device, $item)) {
                        return $os;
                    }
                }
            }
        }

        // check include files
        $os = null;
        $pattern = Config::get('install_dir') . '/includes/discovery/os/*.inc.php';
        foreach (glob($pattern) as $file) {
            include $file;
            if (isset($os)) {
                return $os;
            }
        }

        // check deferred os
        foreach ($deferred_os as $os) {
            if (isset($os_defs[$os]['discovery'])) {
                foreach ($os_defs[$os]['discovery'] as $item) {
                    if ($this->check($device, $item)) {
                        return $os;
                    }
                }
            }
        }

        return 'generic';
    }

    /**
     * Check an array of conditions if all match, return true
     * sysObjectID if sysObjectID starts with any of the values under this item
     * sysDescr if sysDescr contains any of the values under this item
     * sysDescr_regex if sysDescr matches any of the regexes under this item
     * snmpget perform an snmpget on `oid` and check if the result contains `value`. Other subkeys: options, mib, mibdir
     *
     * Appending _except to any condition will invert the match.
     *
     * @param array $device
     * @param array $array Array of items, keys should be sysObjectID, sysDescr, or sysDescr_regex
     * @return bool the result (all items passed return true)
     */
    public function check($device, $array)
    {
        // all items must be true
        foreach ($array as $key => $value) {
            if ($check = ends_with($key, '_except')) {
                $key = substr($key, 0, -7);
            }

            if ($key == 'sysObjectID') {
                if (starts_with($device['sysObjectID'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'sysDescr') {
                if (str_contains($device['sysDescr'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'sysDescr_regex') {
                if (preg_match_any($device['sysDescr'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'sysObjectID_regex') {
                if (preg_match_any($device['sysObjectID'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'snmpget') {
                $options = isset($value['options']) ? $value['options'] : '-Oqv';
                $mib = isset($value['mib']) ? $value['mib'] : null;
                $mib_dir = isset($value['mib_dir']) ? $value['mib_dir'] : null;
                $op = isset($value['op']) ? $value['op'] : 'contains';

                $get_value = snmp_get($device, $value['oid'], $options, $mib, $mib_dir);
                if (compare_var($get_value, $value['value'], $op) == $check) {
                    return false;
                }
            }
        }

        return true;
    }
}
