<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Authentication\Auth;

$no_refresh = true;
$param = array();

if ($vars['action'] == 'expunge' && Auth::user()->hasGlobalAdmin()) {
    dbQuery('TRUNCATE TABLE `eventlog`');
    print_message('Event log truncated');
}

$pagetitle[] = 'Eventlog';
?>

<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>Eventlog</strong>
    </div>

    <?php
    require_once 'includes/common/eventlog.inc.php';
    echo implode('', $common_output);
    ?>
</div>

<script>
    $('.actionBar').append(
        '<div class="pull-left">' +
        '<form method="post" action="" class="form-inline" role="form" id="result_form">' +
        '<div class="form-group">' +
        '<label><strong>Device&nbsp;&nbsp;</strong></label>' +
        '<select name="device" id="device" class="form-control input-sm">' +
        '<option value="">All Devices</option>' +
        <?php
        foreach (get_all_devices() as $data) {
            if (device_permitted($data['device_id'])) {
                echo "'<option value=\"" . $data['device_id'] . "\"";
                if ($data['device_id'] == $_POST['device']) {
                    echo ' selected';
                }

                echo ">" . format_hostname($data) . "</option>' + ";
            }
        }
        ?>
        '</select>' +
        '</div>&nbsp;&nbsp;&nbsp;&nbsp;' +
        '<div class="form-group"><label><strong>Type&nbsp;&nbsp;</strong></label>' +
        '<select name="eventtype" id="eventtype" class="form-control input-sm">' +
        '<option value="">All types</option>' +
        <?php

        foreach (dbFetchColumn("SELECT `type` FROM `eventlog` GROUP BY `type`") as $type) {
            echo "'<option value=\"" . $type . "\"";
            if ($type === $_POST['eventtype']) {
                echo " selected";
            }
            echo ">" . $type . "</option>' + ";
        }

        ?>
        '</select>' +
        '</div>&nbsp;&nbsp;' +
        '<button type="submit" class="btn btn-default input-sm">Filter</button>' +
        '</form>' +
        '</div>'
    );
</script>



