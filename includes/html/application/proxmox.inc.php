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
 * See https://www.gnu.org/licenses/gpl.txt for the full license
 */

/**
 * Fetch all VM's in a Proxmox Cluster
 * @param string $c Clustername
 * @return array An array with all the VM's on this cluster
 */
function proxmox_cluster_vms($c)
{
    return dbFetchRows('SELECT * FROM proxmox WHERE cluster = ? ORDER BY vmid', [$c]);
}

/**
 * Fetch all VM's on a Proxmox node
 * @param int $n device_id
 * @return array An array with all the VM's on this node
 */
function proxmox_node_vms($n)
{
    return dbFetchRows('SELECT * FROM proxmox WHERE device_id = ? ORDER BY vmid', [$n]);
}

/**
 * Fetch all info about a Proxmox VM
 * @param int $vmid Proxmox VM ID
 * @param string $c Clustername
 * @return array An array with all info of this VM on this cluster, including ports
 */
function proxmox_vm_info($vmid, $c)
{
    $vm = dbFetchRow('SELECT pm.*, d.hostname AS host, d.device_id FROM proxmox pm, devices d WHERE pm.device_id = d.device_id AND pm.vmid = ? AND pm.cluster = ?', [$vmid, $c]);
    $appid = dbFetchRow('SELECT app_id FROM applications WHERE device_id = ? AND app_type = ?', [$vm['device_id'], 'proxmox']);

    $vm['ports'] = dbFetchRows('SELECT * FROM proxmox_ports WHERE vm_id = ?', [$vm['id']]);
    $vm['app_id'] = $appid['app_id'];

    return $vm;
}
