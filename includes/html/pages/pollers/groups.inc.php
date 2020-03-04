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

use \LibreNMS\Config;

$pagetitle[] = 'Poller Groups';

require_once 'includes/html/modal/poller_groups.inc.php';

?>
<br />
<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#poller-groups">Create new poller group</button>
<br /><br />
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover table-condensed">
        <tr>
            <th>ID</th>
            <th>Group Name</th>
            <th>Devices</th>
            <th>Description</th>
            <th>Action</th>
        </tr>
<?php

$default_group = ['id' => 0,
                  'group_name' => 'General',
                  'descr' => ''];

$group_list = dbFetchRows('SELECT * FROM `poller_groups`');

$default_poller = Config::get('distributed_poller_group');

array_unshift($group_list, $default_group);

foreach ($group_list as $group) {
    $group_device_count = dbFetchCell('SELECT COUNT(*) FROM devices WHERE `poller_group`=?', $group['id']);

    $group_name = $group['group_name'];
    if ($group['id'] == $default_poller) {
        $group_name .= ' (default Poller)';
    }

    echo '
        <tr id="'.$group['id'].'">
            <td>'.$group['id'].'</td>
            <td>'.$group_name.'</td>
            <td><a href="/devices/poller_group='.$group['id'].'")">'.$group_device_count.'</a></td>
            <td>'.$group['descr'].'</td>';
    echo '<td>';
    if ($group['id']) {
        echo '<button type="button" class="btn btn-success btn-xs" id="'.$group['id'].'" data-group_id="'.$group['id'].'" data-toggle="modal" data-target="#poller-groups">Edit</button> <button type="button" class="btn btn-danger btn-xs" id="'.$group['id'].'" data-group_id="'.$group['id'].'" data-toggle="modal" data-target="#confirm-delete">Delete</button>';
    }
    echo '</td>
        </tr>
';
}

?>

    </table>
</div>
