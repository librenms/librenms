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
    $displayLists .= '<a href="' . request()->fullUrlWithQuery(['format' => 'list_' . $option]) . '" class="sync-url">' . $text . '</a>';
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
    $displayLists .= '<a href="' . request()->fullUrlWithQuery(['format' => 'graph_' . $option]) . '" class="sync-url">' . $text . '</a>';
    if ($vars['format'] == 'graph_' . $option) {
        $displayLists .= '</span>';
    }
    $sep = ' | ';
}
if (isset($vars['group']) && $vars['group']) {
    $displayLists .= ' | <a href="' . url('iftype/group=' . $vars['group']) . '"><i class="fa fa-chart-area fa-fw fa-lg"></i></a>';
}

$displayLists .= '<div style="float: right;">';

$hideSearch = isset($vars['searchbar']) && $vars['searchbar'] == 'hide';
if ($hideSearch) {
    $displayLists .= '<a href="' . request()->fullUrlWithoutQuery('searchbar') . '" class="sync-url">Search</a>';
} else {
    $displayLists .= '<a href="' . request()->fullUrlWithQuery(['searchbar' => 'hide']) . '" class="sync-url">Search</a>';
}

$displayLists .= ' | ';

if (isset($vars['bare']) && $vars['bare'] == 'yes') {
    $displayLists .= '<a href="' . request()->fullUrlWithoutQuery('bare') . '" class="sync-url">Header</a>';
} else {
    $displayLists .= '<a href="' . request()->fullUrlWithQuery(['bare' => 'yes']) . '" class="sync-url">Header</a>';
}

$displayLists .= ' | ';
$displayLists .= '<span style="font-weight: bold;">Bulk actions</span> &#187';

$displayLists .= '<a href="' . request()->fullUrlWithQuery(['filter' => ['deleted' => 1], 'purge' => 'all']) . '" class="sync-url" title="Delete ports"> Purge all deleted</a>';

$displayLists .= '</div>';

$filterFields = [
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
echo <<<'HTML'
<script>
$(window).on('filter:apply', function (event) {
    const serializedFilter = $.param({ filter: event.originalEvent.detail.formatted });

    // update navigation links
    $('a.sync-url').each(function () {
        const url = new URL($(this).attr('href'), window.location.origin);

        // Remove existing filter keys
        [...url.searchParams.keys()]
            .filter(key => key.startsWith('filter'))
            .forEach(key => url.searchParams.delete(key));

        // Build new href, appending new filter params
        const base = url.origin + url.pathname;
        const existing = url.searchParams.toString();
        $(this).attr('href', `${base}?${existing}${existing ? '&' : ''}${serializedFilter}`);
    });
});
</script>
HTML;
