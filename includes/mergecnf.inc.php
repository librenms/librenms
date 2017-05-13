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
 * */

/**
 * Merge config function
 * @author f0o <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Config
 */


/**
 * merge the database config with the global config
 * Global config overrides db
 */
function mergedb()
{
    global $config;

    $db_config = array();
    foreach (dbFetchRows('SELECT `config_name`,`config_value` FROM `config`') as $obj) {
        assign_array_by_path($db_config, $obj['config_name'], $obj['config_value']);
    }
    $config = array_replace_recursive($db_config, $config);
}

/**
 * Assign a value into the passed array by a path
 * 'snmp.version' = 'v1' becomes $arr['snmp']['version'] = 'v1'
 *
 * @param array $arr the array to insert the value into, will be modified in place
 * @param string $path the path to insert the value at
 * @param mixed $value the value to insert, will be type cast
 * @param string $separator path separator
 */
function assign_array_by_path(&$arr, $path, $value, $separator = '.')
{
    // type cast value. Is this needed here?
    if (filter_var($value, FILTER_VALIDATE_INT)) {
        $value = (int) $value;
    } elseif (filter_var($value, FILTER_VALIDATE_FLOAT)) {
        $value = (float) $value;
    } elseif (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null) {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    $keys = explode($separator, $path);

    // walk the array creating keys if they don't exist
    foreach ($keys as $key) {
        $arr = &$arr[$key];
    }
   // assign the variable
    $arr = $value;
}
