<?php
/*
 * Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Device-Grouping
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Devices
 */


/**
 * Generate SQL from Group-Pattern
 * @param string $pattern Pattern to generate SQL for
 * @param string $search  What to searchid for
 * @return string
 */
function GenGroupSQL($pattern, $search='') {
    $pattern = RunGroupMacros($pattern);
    if ($pattern === false) {
       return false;
    }
    $tmp    = explode(' ', $pattern);
    $tables = array();
    foreach ($tmp as $opt) {
        if (strstr($opt, '%') && strstr($opt, '.')) {
            $tmpp     = explode('.', $opt, 2);
            $tmpp[0]  = str_replace('%', '', $tmpp[0]);
            $tables[] = mres(str_replace('(', '', $tmpp[0]));
            $pattern  = str_replace($opt, $tmpp[0].'.'.$tmpp[1], $pattern);
        }
    }

    $pattern = rtrim($pattern, '&&');
    $pattern = rtrim($pattern, '||');

    $tables = array_keys(array_flip($tables));
    $x      = sizeof($tables);
    $i      = 0;
    $join   = '';
    while ($i < $x) {
        if (isset($tables[($i + 1)])) {
            $join .= $tables[$i].'.device_id = '.$tables[($i + 1)].'.device_id && ';
        }

        $i++;
    }

    if (!empty($search)) {
        $search .= ' &&';
    }

    $sql = 'SELECT DISTINCT('.str_replace('(', '', $tables[0]).'.device_id) FROM '.implode(',', $tables).' WHERE '.$search.' ('.str_replace(array('%', '@', '!~', '~'), array('', '.*', 'NOT REGEXP', 'REGEXP'), $pattern).')';
    return $sql;

}//end GenGroupSQL()


/**
 * Get all devices of Group
 * @param integer $group_id Group-ID
 * @return string
 */
function GetDevicesFromGroup($group_id) {
    $pattern     = dbFetchCell('SELECT pattern FROM device_groups WHERE id = ?', array($group_id));
    $pattern = rtrim($pattern, '&&');
    $pattern = rtrim($pattern, '||');
    if (!empty($pattern)) {
        return dbFetchRows(GenGroupSQL($pattern));
    }

    return false;

}//end GetDevicesFromGroup()


/**
 * Get all Device-Groups
 * @return array
 */
function GetDeviceGroups() {
    return dbFetchRows('SELECT * FROM device_groups ORDER BY name');

}//end GetDeviceGroups()


/**
 * Get all groups of Device
 * @param integer $device Device-ID
 * @return array
 */
function GetGroupsFromDevice($device) {
    $ret = array();
    foreach (GetDeviceGroups() as $group) {
        if (dbFetchCell(GenGroupSQL($group['pattern'], 'device_id=?').' LIMIT 1', array($device)) == $device) {
            $ret[] = $group['id'];
        }
    }

    return $ret;

}//end GetGroupsFromDevice()

/**
 * Process Macros
 * @param string $rule Rule to process
 * @param int $x Recursion-Anchor
 * @return string|boolean
 */
function RunGroupMacros($rule,$x=1) {
    global $config;
    krsort($config['alert']['macros']['group']);
    foreach( $config['alert']['macros']['group'] as $macro=>$value ) {
        if( !strstr($macro," ") ) {
            $rule = str_replace('%macros.'.$macro,'('.$value.')',$rule);
        }
    }
    if( strstr($rule,"%macros") ) {
        if( ++$x < 30 ) {
            $rule = RunGroupMacros($rule,$x);
        } else {
            return false;
        }
    }
    return $rule;
}//end RunGroupMacros()
