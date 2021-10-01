<?php

require_once 'includes/html/application/proxmox.inc.php';
$graphs['proxmox'] = [
    'netif',
];

$pmxcl = dbFetchRows('SELECT DISTINCT(`app_instance`) FROM `applications` WHERE `app_type` = ?', ['proxmox']);

print_optionbar_start();

echo "<span style='font-weight: bold;'>Proxmox Clusters</span> &#187; ";

unset($sep);

foreach ($pmxcl as $pmxc) {
    if (isset($sep)) {
        echo $sep;
    }

    if (var_eq('instance', $pmxc['app_instance']) || (! isset($vars['instance']) && ! isset($sep))) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link(\LibreNMS\Util\StringHelpers::niceCase($pmxc['app_instance']), ['page' => 'apps', 'app' => 'proxmox', 'instance' => $pmxc['app_instance']]);

    if (var_eq('instance', $pmxc['app_instance'])) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

$pagetitle[] = 'Proxmox';
$pagetitle[] = $instance;

if (! isset($vars['instance'])) {
    $instance = $pmxcl[0]['app_instance'];
} else {
    $instance = var_get('instance');
}

if (isset($vars['vmid'])) {
    include 'includes/html/pages/apps/proxmox/vm.inc.php';
    $pagetitle[] = $vars['vmid'];
} else {
    echo '
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="row">';
    foreach (proxmox_cluster_vms($instance) as $pmxvm) {
        echo '
                <div class="col-sm-4 col-md-3 col-lg-2">' . generate_link($pmxvm['vmid'] . ' (' . $pmxvm['description'] . ')', ['page' => 'apps', 'app' => 'proxmox', 'instance' => $instance, 'vmid' => $pmxvm['vmid']]) . '</div>';
    }
    echo '
            </div>
        </div>
    </div>
</div>
';
}
