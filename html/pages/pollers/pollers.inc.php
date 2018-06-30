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

use LibreNMS\Authentication\Auth;
use LibreNMS\Config;

require_once 'includes/modal/delete_poller.inc.php';

?>
<br />
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover table-condensed">
        <tr>
            <th>Poller Name</th>
            <th>Devices Polled</th>
            <th>Total Poll Time</th>
            <th>Last Ran</th>
            <th>Actions</th>
        </tr>

<?php
$query = 'SELECT *,UNIX_TIMESTAMP(NOW()) AS `now`, UNIX_TIMESTAMP(`last_polled`) AS `then` FROM `pollers` ORDER BY poller_name';

foreach (dbFetchRows($query) as $poller) {
    $old = ($poller['now'] - $poller['then']);
    $step = Config::get('rrd.step', 300);

    if ($old >= $step) {
        $row_class = 'danger';
    } elseif ($old >= ($step * 0.95)) {
        $row_class = 'warning';
    } else {
        $row_class = 'success';
    }

    $actions = "";
    if (Auth::user()->hasGlobalAdmin() && $old > ($step * 2)) {
        // missed 2 polls show delete button
        $actions .= "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-id='{$poller['id']}' name='delete-poller'><i class='fa fa-trash' aria-hidden='true'></i></button>";
    }

    echo '
        <tr class="'.$row_class.'" id="row_' . $poller['id'] . '">
            <td>'.$poller['poller_name'].'</td>
            <td>'.$poller['devices'].'</td>
            <td>'.$poller['time_taken'].' Seconds</td>
            <td>'.$poller['last_polled'].'</td>
            <td>'.$actions.'</td>
        </tr>
';
}

?>

    </table>
</div>
