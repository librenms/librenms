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
if(is_admin() !== false) {
    if (is_numeric($vars['port'])) {
        $port = dbFetchRow('SELECT * FROM `ports` AS P, `devices` AS D WHERE `port_id` = ? AND D.device_id = P.device_id', array($vars['port']));
    }
?>

 <div class="modal fade bs-example-modal-sm" id="create-bill" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="Create">Add Traffic Bill</h4>
        </div>
        <div class="modal-body">
            <form method="post" role="form" action="bills/" class="form-horizontal alerts-form">
                <input type="hidden" name="addbill" value="yes" />

<?php
    if (is_array($port)) {
        $portalias = (empty($port['ifAlias']) ? '' : ' - '.$port['ifAlias'].'');
?>
    <div class="well">
        <input type="hidden" name="port" value="<?php echo $port['port_id'] ?>" />
        <p>
            <?php echo generate_device_link($port) ?>
            <i class="fa fa-random"></i>
            <?php echo generate_port_link($port, $port['ifName'] . $portalias) ?>
        </p>
    </div>
<?php
        $bill_data['bill_name'] = $port['port_descr_descr'];
        $bill_data['bill_ref'] = $port['port_descr_circuit'];
        $bill_data['bill_notes'] = $port['port_descr_speed'];
    }

    $bill_data['bill_type'] = 'cdr';
    $quota = array('select_gb' => ' selected');
    $cdr = array('select_mbps' => ' selected');
    include 'pages/bill/addoreditbill.inc.php';
?>
    <div class="form-group">
      <div class="col-sm-offset-4 col-sm-4">
        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Add Bill</button>
      </div>
    </div>

            </form>
        </div>
      </div>
    </div>
</div>

<?php

}
