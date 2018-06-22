<?php
/**
 * helpers.php
 *
 *
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
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 *
 *
 * Order of Contents
 * - Common
 *  - d_echo()
 *  - array_pairs()
 *
 * - Formatting
 *  - format_bytes_billing()
 *  - format_bytes_billing_short()
 *  - format_si()
 *  - format_bi()
 *  - format_number()
 *
 * - Services
 *  - list_available_services()
 *
 * - Validation
 */
use Librenms\Config;

/*
 * convenience function - please use this instead of 'if ($debug) { echo ...; }'
 */
function d_echo($text, $no_debug_text = null)
{
    global $debug, $php_debug;
    if ($debug) {
        if (isset($php_debug)) {
            $php_debug[] = $text;
        } else {
            print_r($text);
        }
    } elseif ($no_debug_text) {
        echo "$no_debug_text";
    }
} // d_echo

/**
 * Get all consecutive pairs of values in an array.
 * [1,2,3,4] -> [[1,2],[2,3],[3,4]]
 *
 * @param array $array
 * @return array
 */
function array_pairs($array)
{
    $pairs = [];

    for ($i = 1; $i < count($array); $i++) {
        $pairs[] = [$array[$i -1], $array[$i]];
    }

    return $pairs;
}

// --- Formatting ----

function format_bytes_billing($value)
{
    return format_number($value, Config::get('billing.base').'B');
}//end format_bytes_billing()


function format_bytes_billing_short($value)
{
    return format_number($value, Config::get('billing.base'), 2, 3);
}//end format_bytes_billing_short()

function format_si($value, $round = '2', $sf = '3')
{
    $neg = 0;
    if ($value < "0") {
        $neg = 1;
        $value = $value * -1;
    }

    if ($value >= "0.1") {
        $sizes = array('', 'k', 'M', 'G', 'T', 'P', 'E');
        $ext = $sizes[0];
        for ($i = 1; (($i < count($sizes)) && ($value >= 1000)); $i++) {
            $value = $value / 1000;
            $ext  = $sizes[$i];
        }
    } else {
        $sizes = array('', 'm', 'u', 'n', 'p');
        $ext = $sizes[0];
        for ($i = 1; (($i < count($sizes)) && ($value != 0) && ($value <= 0.1)); $i++) {
            $value = $value * 1000;
            $ext  = $sizes[$i];
        }
    }

    if ($neg == 1) {
        $value = $value * -1;
    }

        return number_format(round($value, $round), $sf, '.', '').$ext;
}

function format_bi($value, $round = '2', $sf = '3')
{
    // This was added becuase when the method was called the $neg variable was being undefined
    $neg = 0;

    if ($value < "0") {
        $neg = 1;
        $value = $value * -1;
    }
    $sizes = array('', 'k', 'M', 'G', 'T', 'P', 'E');
    $ext = $sizes[0];
    for ($i = 1; (($i < count($sizes)) && ($value >= 1024)); $i++) {
        $value = $value / 1024;
        $ext  = $sizes[$i];
    }

    if ($neg) {
        $value = $value * -1;
    }

    return number_format(round($value, $round), $sf, '.', '').$ext;
}

function format_number($value, $base = '1000', $round = 2, $sf = 3)
{
    if ($base == '1000') {
        return format_si($value, $round, $sf);
    } else {
        return format_bi($value, $round, $sf);
    }
}


// ---- SERVICES ----

/**
 * List all available services from nagios plugins directory
 *
 * @return array
 */
function list_available_services()
{
    $services = array();
    foreach (scandir(Config::get('nagios_plugins')) as $file) {
        if (substr($file, 0, 6) === 'check_') {
            $services[] = substr($file, 6);
        }
    }
    return $services;
}


/**
 * Checks if the $hostname provided exists in the DB already
 *
 * @param string $hostname The hostname to check for
 * @param string $sysName The sysName to check
 * @return bool true if hostname already exists
 *              false if hostname doesn't exist
 */
function host_exists($hostname, $sysName = null)
{
    $query = \App\Models\Device::where('hostname', $hostname);

    if (!empty($sysName) && !Config::get('allow_duplicate_sysName')) {
        $query->orWhere('sysName', $sysName);
        if (!empty(Config::get('mydomain'))) {
            $query->orWhere('sysName', rtrim($sysName, '.') . '.' . Config::get('mydomain'));
        }
    }
    return $query->count() > 0;
}
