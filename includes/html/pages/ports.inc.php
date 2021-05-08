<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

use App\Models\Port;
use Illuminate\Database\Eloquent\ModelNotFoundException;

$pagetitle[] = 'Ports';

// Set Defaults here

if (! isset($vars['format'])) {
    $vars['format'] = 'list_basic';
}

$displayLists = '';
$displayLists .= '<span style="font-weight: bold;">Ports lists</span> &#187; ';

$menu_options = ['basic' => 'Basic', 'detail' => 'Detail'];

$sep = '';
foreach ($menu_options as $option => $text) {
    $displayLists .= $sep;
    if ($vars['format'] == 'list_' . $option) {
        $displayLists .= '<span class="pagemenu-selected">';
    }
    $displayLists .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['format' => 'list_' . $option]) . '">' . $text . '</a>';
    if ($vars['format'] == 'list_' . $option) {
        $displayLists .= '</span>';
    }
    $sep = ' | ';
}
$displayLists .= '&nbsp;&nbsp;<span style="font-weight: bold;">Graphs</span> &#187;&nbsp;';

$menu_options = ['bits' => 'Bits',
    'upkts' => 'Unicast Packets',
    'nupkts' => 'Non-Unicast Packets',
    'errors' => 'Errors', ];

$sep = '';
foreach ($menu_options as $option => $text) {
    $displayLists .= $sep;
    if ($vars['format'] == 'graph_' . $option) {
        $displayLists .= '<span class="pagemenu-selected">';
    }
    $displayLists .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['format' => 'graph_' . $option]) . '">' . $text . '</a>';
    if ($vars['format'] == 'graph_' . $option) {
        $displayLists .= '</span>';
    }
    $sep = ' | ';
}

$displayLists .= '<div style="float: right;">';
$displayLists .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['format' => '', 'page' => 'csv.php', 'report' => 'ports']) . '" title="Export as CSV" target="_blank" rel="noopener">Export CSV</a> | <a href="' . \LibreNMS\Util\Url::generate($vars) . '" title="Update the browser URL to reflect the search criteria.">Update URL</a> | ';

if (isset($vars['searchbar']) && $vars['searchbar'] == 'hide') {
    $displayLists .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['searchbar' => '']) . '">Search</a>';
} else {
    $displayLists .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['searchbar' => 'hide']) . '">Search</a>';
}

$displayLists .= ' | ';

if (isset($vars['bare']) && $vars['bare'] == 'yes') {
    $displayLists .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['bare' => '']) . '">Header</a>';
} else {
    $displayLists .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['bare' => 'yes']) . '">Header</a>';
}

$displayLists .= ' | ';
$displayLists .= '<span style="font-weight: bold;">Bulk actions</span> &#187';

$displayLists .= '<a href="ports/deleted=yes/purge=all" title="Delete ports"> Purge all deleted</a>';

$displayLists .= '</div>';

if ((isset($vars['searchbar']) && $vars['searchbar'] != 'hide') || ! isset($vars['searchbar'])) {
    $output = "<div class='pull-left'>";
    $output .= "<form method='post' action='' class='form-inline' role='form'>";
    $output .= addslashes(csrf_field());
    $output .= "<div style='margin-bottom:4px;text-align:left;'>";
    $output .= "<div class='form-group'>";
    $output .= "<select name='device_id' id='device_id' class='form-control input-sm'>";
    $output .= "<option value=''>All Devices</option>";

    if (Auth::user()->hasGlobalRead()) {
        $results = dbFetchRows('SELECT `device_id`,`hostname`, `sysName` FROM `devices` ORDER BY `hostname`');
    } else {
        $results = dbFetchRows('SELECT `D`.`device_id`,`D`.`hostname`, `D`.`sysname` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` ORDER BY `hostname`', [Auth::id()]);
    }
    foreach ($results as $data) {
        if ($data['device_id'] == $vars['device_id']) {
            $deviceselected = 'selected';
        } else {
            $deviceselected = '';
        }
        $ui_device = strlen(format_hostname($data)) > 15 ? substr(format_hostname($data), 0, 15) . '...' : format_hostname($data);
        $output .= "<option value='" . $data['device_id'] . "' " . $deviceselected . '>' . $ui_device . '</option>';
    }

    if (! Auth::user()->hasGlobalRead()) {
        $results = dbFetchRows('SELECT `D`.`device_id`,`D`.`hostname`, `D`.`sysName` FROM `ports` AS `I` JOIN `devices` AS `D` ON `D`.`device_id`=`I`.`device_id` JOIN `ports_perms` AS `PP` ON `PP`.`port_id`=`I`.`port_id` WHERE `PP`.`user_id` = ? AND `PP`.`port_id` = `I`.`port_id` ORDER BY `hostname`', [Auth::id()]);
    } else {
        $results = [];
    }

    foreach ($results as $data) {
        if ($data['device_id'] == $vars['device_id']) {
            $deviceselected = 'selected';
        } else {
            $deviceselected = '';
        }
        $output .= "<option value='" . $data['device_id'] . "' " . $deviceselected . '>' . format_hostname($data) . '</option>';
    }

    $output .= '</select>&nbsp;';

    if (strlen($vars['hostname'])) {
        $hasvalue = "value='" . $vars['hostname'] . "'";
    } else {
        $hasvalue = '';
    }

    $output .= "<input type='text' name='hostname' id='hostname' title='Hostname' class='form-control input-sm' " . $hasvalue . " placeholder='Hostname'>";

    $output .= '</div>&nbsp;';

    switch ($vars['state']) {
        case 'up':
            $isup = 'selected';
            $isdown = '';
            $admindown = '';
            break;
        case 'down':
            $isup = '';
            $isdown = 'selected';
            $admindown = '';
            break;
        case 'admindown':
            $isup = '';
            $isdown = '';
            $admindown = 'selected';
            break;
    }

    $output .= "<div class='form-group'>";
    $output .= "<select name='state' id='state' class='form-control input-sm'>";
    $output .= "<option value=''>All States</option>";
    $output .= "<option value='up' " . $isup . '>Up</option>';
    $output .= "<option value='down' " . $isdown . '>Down</option>';
    $output .= "<option value='admindown' " . $admindown . '>Shutdown</option>';
    $output .= '</select>&nbsp;';

    $output .= "<select name='ifSpeed' id='ifSpeed' class='form-control input-sm'>";
    $output .= "<option value=''>All Speeds</option>";

    $ifSpeed = Port::select('ifSpeed')
        ->hasAccess(Auth::user())
        ->groupBy('ifSpeed')
        ->orderBy('ifSpeed')
        ->get();

    foreach ($ifSpeed as $data) {
        if ($data['ifSpeed']) {
            if ($data['ifSpeed'] == $vars['ifSpeed']) {
                $speedselected = 'selected';
            } else {
                $speedselected = '';
            }
            $output .= "<option value='" . $data['ifSpeed'] . "'" . $speedselected . '>' . \LibreNMS\Util\Number::formatSi($data['ifSpeed'], 2, 3, 'bps') . '</option>';
        }
    }

    $output .= '</select>&nbsp;';
    $output .= '</div>';
    $output .= "<div class='form-group'>";
    $output .= "<select name='ifType' id='ifType' class='form-control input-sm'>";
    $output .= "<option value=''>All Media</option>";

    $ifType = Port::select('ifType')
        ->hasAccess(Auth::user())
        ->groupBy('ifType')
        ->orderBy('ifType')
        ->get();

    foreach ($ifType as $data) {
        if ($data['ifType']) {
            if ($data['ifType'] == $vars['ifType']) {
                $dataselected = 'selected';
            } else {
                $dataselected = '';
            }
            $output .= "<option value='" . clean_bootgrid($data['ifType']) . "' " . $dataselected . '>' . clean_bootgrid($data['ifType']) . '</option>';
        }
    }

    $output .= '</select>&nbsp;';
    $output .= "<select name='port_descr_type' id='port_descr_type' class='form-control input-sm'>";
    $output .= "<option value=''>All Port Types</option>";

    if (Auth::user()->hasGlobalRead()) {
        $sql = 'SELECT `port_descr_type` FROM `ports` GROUP BY `port_descr_type` ORDER BY `port_descr_type`';
    } else {
        $sql = 'SELECT `port_descr_type` FROM `ports` AS `I`, `devices` AS `D`, `devices_perms` AS `P`, `ports_perms` AS `PP` WHERE ((`P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id`) OR (`PP`.`user_id` = ? AND `PP`.`port_id` = `I`.`port_id` AND `I`.`device_id` = `D`.`device_id`)) AND `D`.`device_id` = `I`.`device_id` GROUP BY `port_descr_type` ORDER BY `port_descr_type`';
        $param[] = [Auth::id(), Auth::id()];
    }
    $port_descr_type = Port::select('port_descr_type')
        ->hasAccess(Auth::user())
        ->groupBy('port_descr_type')
        ->orderBy('port_descr_type')
        ->get();

    foreach ($port_descr_type as $data) {
        if ($data['port_descr_type']) {
            if ($data['port_descr_type'] == $vars['port_descr_type']) {
                $portdescrib = 'selected';
            } else {
                $portdescrib = '';
            }
            $output .= "<option value='" . clean_bootgrid($data['port_descr_type']) . "' " . $portdescrib . '>' . ucfirst(clean_bootgrid($data['port_descr_type'])) . '</option>';
        }
    }

    $output .= '</select>&nbsp;';
    $output .= '</div>';
    $output .= "<div class='form-group'>";

    if (strlen($vars['ifAlias'])) {
        $ifaliasvalue = "value='" . $vars['ifAlias'] . "'";
    }

    $output .= '</div>';

    $output .= '</div>';
    $output .= "<div style='text-align:left;'>";

    $output .= "<input title='Port Description' type='text' name='ifAlias' id='ifAlias' class='form-control input-sm' " . $ifaliasvalue . " placeholder='Port Description'>&nbsp;";
    $output .= "<select title='Location' name='location' id='location' class='form-control input-sm'>&nbsp;";
    $output .= "<option value=''>All Locations</option>";

    foreach (getlocations() as $location_row) {
        $location = $location_row['location'];
        $location_id = $location_row['id'];
        if ($location) {
            if ($location_id == $vars['location']) {
                $locationselected = 'selected';
            } else {
                $locationselected = '';
            }
            $ui_location = strlen($location) > 15 ? substr($location, 0, 15) . '...' : $location;
            $output .= "<option value='$location_id' $locationselected>" . clean_bootgrid($ui_location) . '</option>';
        }
    }

    $output .= '</select>&nbsp;';

    if ($vars['ignore']) {
        $ignorecheck = 'checked';
    } else {
        $ignorecheck = '';
    }

    if ($vars['disabled']) {
        $disabledcheck = 'checked';
    } else {
        $disabledcheck = '';
    }

    if ($vars['deleted']) {
        $deletedcheck = 'checked';
    } else {
        $deletedcheck = '';
    }

    $output .= "<label for='ignore'>Ignored</label>&nbsp;";
    $output .= "<input type='checkbox' id='ignore' name='ignore' value='1' " . $ignorecheck . '>&nbsp;';
    $output .= "<label for='disabled'>Disabled</label>&nbsp;";
    $output .= "<input type='checkbox' id='disabled' name='disabled' value='1' " . $disabledcheck . '>&nbsp;';
    $output .= "<label for='deleted'>Deleted</label>&nbsp;";
    $output .= "<input type='checkbox' id='deleted' name='deleted' value='1' " . $deletedcheck . '>&nbsp;';

    $output .= "<button type='submit' class='btn btn-default btn-sm'>Search</button>&nbsp;";
    $output .= "<a class='btn btn-default btn-sm' href='" . \LibreNMS\Util\Url::generate(['page' => 'ports', 'section' => $vars['section'], 'bare' => $vars['bare']]) . "' title='Reset critera to default.'>Reset</a>";

    $output .= '</div>';

    $output .= '</form>';
    $output .= '</div>';
}

$param = [];

if (! isset($vars['ignore'])) {
    $vars['ignore'] = '0';
}
if (! isset($vars['disabled'])) {
    $vars['disabled'] = '0';
}
if (! isset($vars['deleted'])) {
    $vars['deleted'] = '0';
}

$where = '';
$ignore_filter = 0;
$disabled_filter = 0;

foreach ($vars as $var => $value) {
    if ($value != '') {
        switch ($var) {
            case 'hostname':
                $where .= ' AND D.hostname LIKE ?';
                $param[] = '%' . $value . '%';
                break;
            case 'location':
                if (is_int($value)) {
                    $where .= ' AND L.id = ?';
                    $param[] = $value;
                } else {
                    $where .= ' AND L.location LIKE ?';
                    $param[] = '%' . $value . '%';
                }
                break;
            case 'device_id':
                $where .= ' AND D.device_id = ?';
                $param[] = $value;
                break;
            case 'deleted':
                if ($value == 1 || $value == 'yes') {
                    $where .= ' AND `I`.`deleted` = 1';
                    $ignore_filter = 1;
                }
                break;
            case 'ignore':
                if ($value == 1 || $value == 'yes') {
                    $where .= ' AND (I.ignore = 1 OR D.ignore = 1) AND I.deleted = 0';
                    $ignore_filter = 1;
                }
                break;
            case 'disabled':
                if ($value == 1 || $value == 'yes') {
                    $where .= ' AND `I`.`disabled` = 1 AND `I`.`deleted` = 0';
                    $disabled_filter = 1;
                }
                break;
            case 'ifSpeed':
                if (is_numeric($value)) {
                    $where .= " AND I.$var = ?";
                    $param[] = $value;
                }
                break;
            case 'ifType':
                $where .= " AND I.$var = ?";
                $param[] = $value;
                break;
            case 'ifAlias':
            case 'port_descr_type':
                $where .= " AND I.$var LIKE ?";
                $param[] = '%' . $value . '%';
                break;
            case 'errors':
                if ($value == 1 || $value == 'yes') {
                    $where .= " AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')";
                }
                break;
            case 'state':
                if ($value == 'down') {
                    $where .= ' AND I.ifAdminStatus = ? AND I.ifOperStatus = ?';
                    $param[] = 'up';
                    $param[] = 'down';
                } elseif ($value == 'up') {
                    $where .= ' AND I.ifAdminStatus = ? AND I.ifOperStatus = ?';
                    $param[] = 'up';
                    $param[] = 'up';
                } elseif ($value == 'admindown') {
                    $where .= ' AND I.ifAdminStatus = ? AND D.ignore = 0';
                    $param[] = 'down';
                }
                break;
            case 'purge':
                if ($vars['purge'] === 'all') {
                    Port::hasAccess(Auth::user())->with(['device' => function ($query) {
                        $query->select('device_id', 'hostname');
                    }])->isDeleted()->chunk(100, function ($ports) {
                        foreach ($ports as $port) {
                            $port->delete();
                        }
                    });
                } else {
                    try {
                        Port::hasAccess(Auth::user())->where('port_id', $vars['purge'])->firstOrFail()->delete();
                    } catch (ModelNotFoundException $e) {
                        echo "<div class='alert alert-danger'>Port ID {$vars['purge']} not found! Could not purge port.</div>";
                    }
                }
                break;
        }
    }
}

if ($ignore_filter == 0 && $disabled_filter == 0) {
    $where .= ' AND `I`.`ignore` = 0 AND `I`.`disabled` = 0 AND `I`.`deleted` = 0';
}

$query = 'SELECT * FROM `ports` AS I, `devices` AS D LEFT JOIN `locations` AS L ON D.location_id = L.id WHERE I.device_id = D.device_id' . $where . ' ' . $query_sort;
$row = 1;

[$format, $subformat] = explode('_', basename($vars['format']));

// only grab list of ports for graph pages, table uses ajax
$ports = $format == 'graph' ? dbFetchRows($query, $param) : [];

switch ($vars['sort']) {
    case 'traffic':
        $ports = array_sort_by_column($ports, 'ifOctets_rate', SORT_DESC);
        break;
    case 'traffic_in':
        $ports = array_sort_by_column($ports, 'ifInOctets_rate', SORT_DESC);
        break;
    case 'traffic_out':
        $ports = array_sort_by_column($ports, 'ifOutOctets_rate', SORT_DESC);
        break;
    case 'packets':
        $ports = array_sort_by_column($ports, 'ifUcastPkts_rate', SORT_DESC);
        break;
    case 'packets_in':
        $ports = array_sort_by_column($ports, 'ifInUcastOctets_rate', SORT_DESC);
        break;
    case 'packets_out':
        $ports = array_sort_by_column($ports, 'ifOutUcastOctets_rate', SORT_DESC);
        break;
    case 'errors':
        $ports = array_sort_by_column($ports, 'ifErrors_rate', SORT_DESC);
        break;
    case 'speed':
        $ports = array_sort_by_column($ports, 'ifSpeed', SORT_DESC);
        break;
    case 'port':
        $ports = array_sort_by_column($ports, 'ifDescr', SORT_ASC);
        break;
    case 'media':
        $ports = array_sort_by_column($ports, 'ifType', SORT_ASC);
        break;
    case 'descr':
        $ports = array_sort_by_column($ports, 'ifAlias', SORT_ASC);
        break;
    case 'device':
    default:
        $ports = array_sort_by_column($ports, 'hostname', SORT_ASC);
}

if (file_exists('includes/html/pages/ports/' . $format . '.inc.php')) {
    require 'includes/html/pages/ports/' . $format . '.inc.php';
}
