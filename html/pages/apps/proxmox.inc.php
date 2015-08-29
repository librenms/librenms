<?php

include('includes/application/proxmox.inc.php');
$graphs['proxmox']    = array(
    'netif'
);

$pmxcl = dbFetchRows("SELECT DISTINCT(`app_instance`) FROM `applications` WHERE `app_type` = ?", array('proxmox'));


print_optionbar_start();

echo "<span style='font-weight: bold;'>Proxmox</span> &#187; ";

unset($sep);

foreach ($pmxcl as $pmxc) {
    if (isset($sep)) {
        echo $sep;
    };

    if (var_eq('instance', $pmxc['app_instance'])) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link(nicecase($pmxc['app_instance']), array('page' => 'apps', 'app' => 'proxmox', 'instance' => $pmxc['app_instance']));

    if (var_eq('instance', $pmxc['app_instance'])) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

if (!var_isset('instance')) {
    echo 'Select a cluster:';
    echo '<ul>';
    foreach ($pmxcl as $pmxc) {
        echo '<li>'.generate_link(nicecase($pmxc['app_instance']), array('page' => 'apps', 'app' => 'proxmox', 'instance' => $pmxc['app_instance'])).'</li>';
    }
    echo '</ul>';
} elseif (!var_isset('vmid')) {
    echo '<ul>';
    foreach (proxmox_cluster_vms(var_get('instance')) as $pmxvm) {
        echo '<li>'.generate_link($pmxvm['vmid']." (".$pmxvm['description'].")", array('page' => 'apps', 'app' => 'proxmox', 'instance' => var_get('instance'), 'vmid' => $pmxvm['vmid'])).'</li>';
    }
    echo '</ul>';
} else {
    include("pages/apps/proxmox/vm.inc.php");
}
   
$pagetitle[] = 'Proxmox';
