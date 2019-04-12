<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$no_refresh = true;


echo '<ul class="nav nav-tabs">';

$poll_tabs = [
    [
        'name' => 'Pollers',
        'icon' => 'fa-th-large',
    ],
];

if (\LibreNMS\Config::get('distributed_poller')) {
    $poll_tabs[] = array(
        'name' => 'Groups',
        'icon' => 'fa-th',
    );
}

$poll_tabs[] = [
    'name' => 'Performance',
    'icon' => 'fa-line-chart',
];
$poll_tabs[] = [
    'name' => 'Log',
    'icon' => 'fa-file-text',
];

$current_tab = basename($vars['tab'] ?? 'pollers');

foreach ($poll_tabs as $tab) {
    $taburl = strtolower($tab['name']);
    echo '<li role="presentation" ' . ($current_tab == $taburl ? ' class="active"' : '') . '><a href="';
    echo generate_url(['page' => 'pollers', 'tab' => $taburl]);
    echo '"><i class="fa ' . $tab['icon'] . ' fa-lg icon-theme" aria-hidden="true"></i> ' . $tab['name'];
    echo '</a></li>';
}

echo '</ul>';

include_once "includes/html/pages/pollers/$current_tab.inc.php";
