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

$displayLists = '<span style="font-weight: bold;">Ports lists</span> &#187; ';

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

$displayLists .= '<a href="ports/deleted=1/purge=all" title="Delete ports"> Purge all deleted</a>';

$displayLists .= '</div>';

if ((isset($vars['searchbar']) && $vars['searchbar'] != 'hide') || ! isset($vars['searchbar'])) {
    $output = "<form method='post' action='' class='form-inline' role='form'>";
    $output .= addslashes(csrf_field());
    $output .= "<div style='margin-bottom:4px;text-align:left;'>";
    $output .= "<div class='form-group'>";
    $output .= "<select name='device_id' id='device_id' class='form-control input-sm'></select>&nbsp;";

    $hasvalue = ! empty($vars['hostname']) ? "value='" . htmlspecialchars($vars['hostname']) . "'" : '';

    $output .= "<input type='text' name='hostname' id='hostname' title='Hostname' class='form-control input-sm' " . $hasvalue . " placeholder='Hostname'>";

    $output .= '</div>&nbsp;';

    switch ($vars['state'] ?? '') {
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
        default:
            $isup = '';
            $isdown = '';
            $admindown = '';
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
            $speedselected = isset($vars['ifSpeed']) && $data['ifSpeed'] == $vars['ifSpeed'] ? 'selected' : '';
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
            $dataselected = isset($vars['ifType']) && $data['ifType'] == $vars['ifType'] ? 'selected' : '';
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
            if (isset($vars['port_descr_type']) && $data['port_descr_type'] == $vars['port_descr_type']) {
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

    $ifaliasvalue = isset($vars['ifAlias']) ? "value='" . htmlspecialchars($vars['ifAlias']) . "'" : '';

    $output .= '</div>';

    $output .= '</div>';
    $output .= "<div style='text-align:left;'>";

    $output .= "<input title='Port Description' type='text' name='ifAlias' id='ifAlias' class='form-control input-sm' " . $ifaliasvalue . " placeholder='Port Description'>&nbsp;";
    $output .= "<select title='Location' name='location' id='location' class='form-control input-sm'></select>&nbsp;";

    $ignorecheck = isset($vars['ignore']) ? 'checked' : '';
    $disabledcheck = isset($vars['disabled']) ? 'checked' : '';
    $deletedcheck = isset($vars['deleted']) ? 'checked' : '';

    $output .= "<label for='ignore'>Ignored</label>&nbsp;";
    $output .= "<input type='checkbox' id='ignore' name='ignore' value='1' " . $ignorecheck . '>&nbsp;';
    $output .= "<label for='disabled'>Disabled</label>&nbsp;";
    $output .= "<input type='checkbox' id='disabled' name='disabled' value='1' " . $disabledcheck . '>&nbsp;';
    $output .= "<label for='deleted'>Deleted</label>&nbsp;";
    $output .= "<input type='checkbox' id='deleted' name='deleted' value='1' " . $deletedcheck . '>&nbsp;';

    $output .= "<button type='submit' class='btn btn-default btn-sm'>Search</button>&nbsp;";
    $output .= "<a class='btn btn-default btn-sm' href='" . \LibreNMS\Util\Url::generate(['page' => 'ports', 'section' => $vars['section'] ?? '', 'bare' => $vars['bare'] ?? '']) . "' title='Reset critera to default.'>Reset</a>";

    $output .= '</div>';

    $output .= '</form>';
}

if (! isset($vars['ignore'])) {
    $vars['ignore'] = '0';
}
if (! isset($vars['disabled'])) {
    $vars['disabled'] = '0';
}
if (! isset($vars['deleted'])) {
    $vars['deleted'] = '0';
}

if (isset($vars['purge'])) {
    if ($vars['purge'] === 'all') {
        Port::hasAccess(Auth::user())->with(['device' => function ($query) {
            $query->select('device_id', 'hostname');
        }])->isDeleted()->chunkById(100, function ($ports) {
            foreach ($ports as $port) {
                $port->delete();
            }
        });
    } else {
        try {
            Port::hasAccess(Auth::user())->where('port_id', $vars['purge'])->firstOrFail()->delete();
        } catch (ModelNotFoundException $e) {
            echo "<div class='alert alert-danger'>Port ID " . htmlspecialchars($vars['purge']) . ' not found! Could not purge port.</div>';
        }
    }
}

[$format, $subformat] = explode('_', basename($vars['format']));

if (file_exists('includes/html/pages/ports/' . $format . '.inc.php')) {
    require 'includes/html/pages/ports/' . $format . '.inc.php';
}
