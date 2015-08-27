<?php

function var_isset($v) {
    global $vars;
    return isset($vars[$v]);
}

function var_eq($v, $t) {
    global $vars;
    if (isset($vars[$v]) && $vars[$v] == $t) {
        return true;
    }

    return false;
}

function var_get($v) {
    global $vars;
    if (isset($vars[$v])) {
        return $vars[$v];
    }

    return false;
}

function proxmox_cluster_vms($c) {
    return dbFetchRows("SELECT * FROM proxmox WHERE cluster = ? ORDER BY vmid", array($c));
}

function proxmox_vm_info($vmid, $c) {
    $vm = dbFetchRow("SELECT pm.*, d.hostname AS host, d.device_id FROM proxmox pm, devices d WHERE pm.device_id = d.device_id AND pm.vmid = ? AND pm.cluster = ?", array($vmid, $c));
    $appid = dbFetchRow("SELECT app_id FROM applications WHERE device_id = ? AND app_type = ?", array($vm['device_id'], 'proxmox'));

    $vm['ports'] = dbFetchRows("SELECT * FROM proxmox_ports WHERE vm_id = ?", array($vm['id']));
    $vm['app_id'] = $appid['app_id'];
    return $vm;
}
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
        echo '<li>'.generate_link($pmxvm['vmid']." (".$pmxvm['description'].")", array('page' => 'proxmox', 'cluster' => $pmxc['app_instance'], 'vmid' => $pmxvm['vmid'])).'</li>';
    }
    echo '</ul>';
} else {
    include("pages/proxmox/vm.inc.php");
}
   
$pagetitle[] = 'Proxmox';
