<?php

if (isset($vars['id']) && is_numeric($vars['id'])) {
    $vminfo = \App\Models\Vminfo::find($vars['id']);

    if ($vminfo && ($auth || device_permitted($vminfo->device_id))) {
        $device = $vminfo->device->toArray();
        $device['hostname'] = $vminfo->device->hostname; // ensure it is available

        $title = " :: VM :: $vminfo->vmwVmDisplayName";
        $auth = true;
    }
}
