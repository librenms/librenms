<?php

function proxmox_cluster_vms($c) {
    return dbFetchRows("SELECT * FROM proxmox WHERE cluster = ? ORDER BY vmid", array($c));
}

function proxmox_node_vms($n) {
    return dbFetchRows("SELECT * FROM proxmox WHERE device_id = ? ORDER BY vmid", array($n));
}

function proxmox_vm_info($vmid, $c) {
    $vm = dbFetchRow("SELECT pm.*, d.hostname AS host, d.device_id FROM proxmox pm, devices d WHERE pm.device_id = d.device_id AND pm.vmid = ? AND pm.cluster = ?", array($vmid, $c));
    $appid = dbFetchRow("SELECT app_id FROM applications WHERE device_id = ? AND app_type = ?", array($vm['device_id'], 'proxmox'));

    $vm['ports'] = dbFetchRows("SELECT * FROM proxmox_ports WHERE vm_id = ?", array($vm['id']));
    $vm['app_id'] = $appid['app_id'];
    return $vm;
}
