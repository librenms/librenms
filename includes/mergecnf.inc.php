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

    $clone = $config;
    foreach (dbFetchRows('select config_name,config_value from config') as $obj) {
        $clone = array_replace_recursive($clone, mergecnf($obj));
    }
    $config = array_replace_recursive($clone, $config);
}


/**
 * @param $obj
 * @return array
 */
function mergecnf($obj)
{
    $pointer = array();
    $val     = $obj['config_value'];
    $obj     = $obj['config_name'];
    $obj     = explode('.', $obj, 2);
    if (!isset($obj[1])) {
        if (filter_var($val, FILTER_VALIDATE_INT)) {
            $val = (int) $val;
        } elseif (filter_var($val, FILTER_VALIDATE_FLOAT)) {
            $val = (float) $val;
        } elseif (filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null) {
            $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }

        if (!empty($obj[0])) {
            return array($obj[0] => $val);
        } else {
            return array($val);
        }
    } else {
        $pointer[$obj[0]] = mergecnf(array('config_name' => $obj[1], 'config_value' => $val));
    }

    return $pointer;
}//end mergecnf()
