<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS
 *
 * @author     LibreNMS Contributors <librenms-project@google.groups.com>
 * @copyright  (C) 2006 - 2012 Adam Armstrong (as Observium)
 * @copyright  (C) 2013 LibreNMS Group
 */

use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Models\Bill;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Enum\ImageFormat;

function bill_permitted($bill_id)
{
    if (Gate::allows('viewAll', Bill::class)) {
        return true;
    }

    return Permissions::canAccessBill($bill_id, Auth::id());
}

function port_permitted($port_id, $device_id = null)
{
    if (Gate::allows('viewAll', Port::class)) {
        return true;
    }

    if (! is_numeric($device_id)) {
        $device_id = PortCache::get((int) $port_id)?->device_id;
    }

    if (device_permitted($device_id)) {
        return true;
    }

    return Permissions::canAccessPort($port_id, Auth::id());
}

function device_permitted($device_id)
{
    if (Gate::allows('viewAll', Device::class)) {
        return true;
    }

    return Permissions::canAccessDevice($device_id, Auth::id());
}

/**
 * Create image to output text instead of a graph.
 *
 * @param  string  $text
 * @param  int[]  $color
 */
function graph_error($text, $short = null, $color = [128, 0, 0])
{
    header('Content-Type: ' . ImageFormat::forGraph()->mediaType());
    echo LibreNMS\Util\Graph::error($text, $short, 300, null, $color);
}

function get_url()
{
    // http://stackoverflow.com/questions/2820723/how-to-get-base-url-with-php
    // http://stackoverflow.com/users/184600/ma%C4%8Dek
    return sprintf(
        '%s://%s%s',
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME'],
        $_SERVER['REQUEST_URI']
    );
}//end get_url()

/**
 * Return the rows from 'ports' for all ports of a certain type as parsed by port_descr_parser.
 * One or an array of strings can be provided as an argument; if an array is passed, all ports matching
 * any of the types in the array are returned.
 *
 * @param  $types  mixed String or strings matching 'port_descr_type's.
 * @return array Rows from the ports table for matching ports.
 */
function get_ports_from_type($given_types)
{
    // Make the arg an array if it isn't, so subsequent steps only have to handle arrays.
    if (! is_array($given_types)) {
        $given_types = [$given_types];
    }

    // Check the config for a '_descr' entry for each argument. This is how a 'custom_descr' entry can
    //  be key/valued to some other string that's actually searched for in the DB. Merge or append the
    //  configured value if it's an array or a string. Or append the argument itself if there's no matching
    //  entry in config.
    $search_types = [];
    foreach ($given_types as $type) {
        if (LibrenmsConfig::has($type . '_descr')) {
            $type_descr = LibrenmsConfig::get($type . '_descr');
            if (is_array($type_descr)) {
                $search_types = array_merge($search_types, $type_descr);
            } else {
                $search_types[] = $type_descr;
            }
        } else {
            $search_types[] = $type;
        }
    }

    // Using the full list of strings to search the DB for, build the 'where' portion of a query that
    //  compares 'port_descr_type' with entry in the list. Also, since '@' is the convential wildcard,
    //  replace it with '%' so it functions as a wildcard in the SQL query.
    $type_where = ' (';
    $or = '';
    $type_param = [];

    foreach ($search_types as $type) {
        if (! empty($type)) {
            $type = strtr($type, '@', '%');
            $type_where .= " $or `port_descr_type` LIKE ?";
            $or = 'OR';
            $type_param[] = $type;
        }
    }
    $type_where .= ') ';

    // Run the query with the generated 'where' and necessary parameters, and send it back.
    $ports = dbFetchRows("SELECT * FROM `ports` as I, `devices` AS D WHERE $type_where AND I.device_id = D.device_id ORDER BY I.ifAlias", $type_param);

    return $ports;
}

/**
 * Return stacked graphs information
 *
 * @param  string  $transparency  value of desired transparency applied to rrdtool options (values 01 - 99)
 * @return array containing transparency and stacked setup
 */
function generate_stacked_graphs($force_stack = false, $transparency = '88')
{
    if (LibrenmsConfig::get('webui.graph_stacked') == true || $force_stack == true) {
        return ['transparency' => $transparency, 'stacked' => '1'];
    } else {
        return ['transparency' => '', 'stacked' => '-1'];
    }
}
