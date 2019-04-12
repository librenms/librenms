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
            <th>Description</th>
            <th>Action</th>
        </tr>

<?php
$query = 'SELECT * FROM `poller_groups`';

foreach (dbFetchRows($query) as $group) {
    echo '
        <tr id="'.$group['id'].'">
            <td>'.$group['id'].'</td>
            <td>'.$group['group_name'].'</td>
            <td>'.$group['descr'].'</td>
            <td><button type="button" class="btn btn-success btn-xs" id="'.$group['id'].'" data-group_id="'.$group['id'].'" data-toggle="modal" data-target="#poller-groups">Edit</button> <button type="button" class="btn btn-danger btn-xs" id="'.$group['id'].'" data-group_id="'.$group['id'].'" data-toggle="modal" data-target="#confirm-delete">Delete</button></td>
        </tr>
';
}

?>

    </table>
</div>
