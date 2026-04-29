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
use Illuminate\Support\Facades\Blade;

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
    $displayLists .= '<a href="' . request()->fullUrlWithQuery(['format' => 'graph_' . $option]) . '">' . $text . '</a>';
    if ($vars['format'] == 'graph_' . $option) {
        $displayLists .= '</span>';
    }
    $sep = ' | ';
}
if (isset($vars['group']) && $vars['group']) {
    $displayLists .= ' | <a href="' . url('iftype/group=' . $vars['group']) . '"><i class="fa fa-chart-area fa-fw fa-lg"></i></a>';
}

$displayLists .= '<div style="float: right;">';

if (isset($vars['searchbar']) && $vars['searchbar'] == 'hide') {
    $displayLists .= '<a href="' . request()->fullUrlWithoutQuery(['searchbar']) . '">Search</a>';
} else {
    $displayLists .= '<a href="' . request()->fullUrlWithQuery(['searchbar' => 'hide']) . '">Search</a>';
}

$displayLists .= ' | ';

if (isset($vars['bare']) && $vars['bare'] == 'yes') {
    $displayLists .= '<a href="' . request()->fullUrlWithoutQuery(['bare' => '']) . '">Header</a>';
} else {
    $displayLists .= '<a href="' . request()->fullUrlWithQuery(['bare' => 'yes']) . '">Header</a>';
}

$displayLists .= ' | ';
$displayLists .= '<span style="font-weight: bold;">Bulk actions</span> &#187';

$displayLists .= '<a href="ports/deleted=1/purge=all" title="Delete ports"> Purge all deleted</a>';

$displayLists .= '</div>';

if ((isset($vars['searchbar']) && $vars['searchbar'] != 'hide') || ! isset($vars['searchbar'])) {
    $fields = [
        [
            'key' => 'device_id',
            'label' => __('Device'),
            'type' => 'select',
            'endpoint' => route('ajax.select.device'),
        ],
        [
            'key' => 'device.location_id',
            'label' => __('Location'),
            'type' => 'select',
            'endpoint' => route('ajax.select.location'),
        ],
        [
            'key' => 'search',
            'label' => 'Description',
            'type' => 'text',
        ],
        [
            'key' => 'state',
            'label' => 'Oper Status',
            'type' => 'select',
            'options' => [
                'up',
                'down',
                'shutdown'
            ],
        ],
        [
            'key' => 'ifSpeed',
            'label' => 'Speed',
            'type' => 'select',
            'endpoint' => route('ajax.select.port-field'),
            'params' => [
                'field' => 'ifSpeed',
            ],
        ],
        [
            'key' => 'ifType',
            'label' => 'Media',
            'type' => 'select',
            'endpoint' => route('ajax.select.port-field'),
            'params' => [
                'field' => 'ifType',
            ],
        ],
        [
            'key' => 'ifDuplex',
            'label' => 'Duplex',
            'type' => 'select',
            'options' => [
                'fullDuplex' => 'Full',
                'halfDuplex' => 'Half',
                'unknown' => 'unknown',
            ],
        ],
        [
            'key' => 'port_type',
            'label' => 'Port Type',
            'type' => 'select',
            'endpoint' => route('ajax.select.port-field'),
            'params' => [
                'field' => 'port_descr_type',
            ],
        ],
        [
            'key' => 'ignore',
            'label' => 'Ignored',
            'type' => 'boolean',
        ],
        [
            'key' => 'disabled',
            'label' => 'Disabled',
            'type' => 'boolean',
        ],
        [
            'key' => 'deleted',
            'label' => 'Deleted',
            'type' => 'boolean',
        ],
    ];

    echo Blade::render('<template id="port-filter-template"><x-filter :fields="$fields" id="port-filter"/></template>', ['fields' => $fields]);
}

if (isset($vars['purge'])) {
    if ($vars['purge'] === 'all') {
        Port::hasAccess(Auth::user())->with(['device' => function ($query): void {
            $query->select('device_id', 'hostname');
        }])->isDeleted()->chunkById(100, function ($ports): void {
            foreach ($ports as $port) {
                $port->delete();
            }
        });
    } else {
        try {
            Port::hasAccess(Auth::user())->where('port_id', $vars['purge'])->firstOrFail()->delete();
        } catch (ModelNotFoundException) {
            echo "<div class='alert alert-danger'>Port ID " . htmlspecialchars((string) $vars['purge']) . ' not found! Could not purge port.</div>';
        }
    }
}

[$format, $subformat] = explode('_', basename((string) $vars['format']));

if (file_exists('includes/html/pages/ports/' . $format . '.inc.php')) {
    require 'includes/html/pages/ports/' . $format . '.inc.php';
}
