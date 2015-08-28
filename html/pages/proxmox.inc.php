<?php

/*
 * Copyright (C) 2015 Mark Schouten <mark@tuxis.nl>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2 dated June,
 * 1991.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * See http://www.gnu.org/licenses/gpl.txt for the full license
 */

include('includes/application/proxmox.inc.php');
$graphs['proxmox']    = array(
    'netif'
);

print_optionbar_start();

echo "<span style='font-weight: bold;'>Proxmox</span> &#187; ";

unset($sep);

foreach ($pmxcl as $pmxc) {
    if (isset($sep)) { echo $sep; };

    if (var_eq('cluster', $pmxc['app_instance'])) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link(nicecase($pmxc['app_instance']), array('page' => 'proxmox', 'cluster' => $pmxc['app_instance']));

    if (var_eq('cluster', $pmxc['app_instance'])) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

if (!var_isset('cluster')) {
    echo 'Select a cluster:';
    echo '<ul>';
    foreach ($pmxcl as $pmxc) {
        echo '<li>'.generate_link(nicecase($pmxc['app_instance']), array('page' => 'proxmox', 'cluster' => $pmxc['app_instance'])).'</li>';
    }
    echo '</ul>';
} elseif (!var_isset('vmid')) {
    echo '<ul>';
    foreach (proxmox_cluster_vms(var_get('cluster')) as $pmxvm) {
        echo '<li>'.generate_link($pmxvm['vmid']." (".$pmxvm['description'].")", array('page' => 'proxmox', 'cluster' => var_get('cluster'), 'vmid' => $pmxvm['vmid'])).'</li>';
    }
    echo '</ul>';
} else {
    include("pages/proxmox/vm.inc.php");
}
   
$pagetitle[] = 'Proxmox';
