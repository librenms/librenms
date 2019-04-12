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

use LibreNMS\Config;

$pagetitle[] = 'Pollers';

require_once 'includes/html/modal/delete_poller.inc.php';

?>
    <br />

<?php
$query = 'SELECT *,UNIX_TIMESTAMP(NOW()) AS `now`, UNIX_TIMESTAMP(`last_polled`) AS `then` FROM `pollers` ORDER BY poller_name';
$rows = dbFetchRows($query);

if (count($rows) !== 0) {
    echo '
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Standard Pollers</h3>
    </div>
    <div class="panel-body">
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover table-condensed">
            <tr>
                <th>Poller Name</th>
                <th>Devices Polled</th>
                <th>Total Poll Time</th>
                <th>Last Ran</th>
                <th>Actions</th>
            </tr>';

    foreach ($rows as $poller) {
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
        if (\Auth::user()->hasGlobalAdmin() && $old > ($step * 2)) {
            // missed 2 polls show delete button
            $actions .= "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-id='{$poller['id']}' data-pollertype='delete-poller' name='delete-poller'><i class='fa fa-trash' aria-hidden='true'></i></button>";
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

    echo '
        </table>
        </div>
    </div>
</div>';
}

$query = 'SELECT *,UNIX_TIMESTAMP(NOW()) AS `now`, UNIX_TIMESTAMP(`last_report`) AS `then` FROM `poller_cluster` ORDER BY poller_name';
$rows = dbFetchRows($query);

if (count($rows) !== 0) {
    echo '
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Poller Cluster Health</h3>
    </div>
    <div class="panel-body">
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <tr>
                <th>Name</th>
                <th>Node ID</th>
                <th>Version</th>
                <th>Groups Served</th>
                <th>Last Checkin</th>
                <th>Cluster Master</th>
                <th>Job</th>
                <th>Workers</th>
                <th>Devices Actioned<br><small>Last Interval</small></th>
                <th>Devices Pending</th>
                <th>Worker Seconds<br><small>Consumed/Maximum</small></th>
                <th>Actions</th>
            </tr>';

    foreach ($rows as $poller) {
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
        if (\Auth::user()->hasGlobalAdmin() && $old > ($step * 2)) {
            // missed 2 polls show delete button
            $actions .= "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-id='{$poller['id']}' data-pollertype='delete-cluster-poller' name='delete-cluster-poller'><i class='fa fa-trash' aria-hidden='true'></i></button>";
        }

        $stat_query = 'SELECT * FROM `poller_cluster_stats` WHERE `parent_poller`=' . $poller['id'] . ';';
        $stat_row = dbFetchRows($stat_query);
        $stat_count = count($stat_row);

        $first_row = true;

        foreach ($stat_row as $stats) {
            // Emit the row container
            echo '<tr class="'.$row_class.'" id="row_' . $poller['id'] . '">';

            if ($first_row) {
                // On the first iteration, print some rowspanned columns
                echo '
                <td rowspan="'.$stat_count.'">'.$poller['poller_name'].'</td>
                <td rowspan="'.$stat_count.'"' . (empty($poller['node_id']) ? ' class="danger"' : '') . '>'.$poller['node_id'].'</td>
                <td rowspan="'.$stat_count.'">'.$poller['poller_version'].'</td>
                <td rowspan="'.$stat_count.'">'.$poller['poller_groups'].'</td>
                <td rowspan="'.$stat_count.'">'.$poller['last_report'].'</td>
                <td rowspan="'.$stat_count.'">'. ($poller['master'] ? "Yes" : "No") .'</td>';
            }

            // Emit the job stats
            echo '
            <td>'.$stats['poller_type'].'</td>
            <td>'.$stats['workers'].'</td>
            <td>'.$stats['devices'].'</td>
            <td>'.$stats['depth'].'</td>
            <td>'.$stats['worker_seconds'].' / '.$stats['frequency']*$stats['workers'].'</td>';

            if ($first_row) {
                // On the first iteration, print some rowspanned columns
                echo '<td rowspan="'.$stat_count.'">'.$actions.'</td>';
            }

            // End the row
            echo '</tr>';
            $first_row = false;
        }
    }
    echo '
        </table>
        <small>
          Worker seconds indicates the maximum polling throughput a node can achieve in perfect conditions. If the consumed is close to the maximum, consider adding more threads, or better tuning your groups.<br>
          If there are devices pending but consumed worker seconds is low, your hardware is not sufficient for the number of devices and the poller cannot reach maximum throughput.
        </small>
        </div>
    </div>
</div>';
}
?>
