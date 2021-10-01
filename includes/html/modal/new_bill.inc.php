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

if (Auth::user()->hasGlobalAdmin()) {
    require 'includes/html/javascript-interfacepicker.inc.php';

    $port_device_id = -1;
    if (is_numeric($vars['port'])) {
        $port = dbFetchRow('SELECT * FROM `ports` AS P, `devices` AS D WHERE `port_id` = ? AND D.device_id = P.device_id', [$vars['port']]);
        $bill_data['bill_name'] = $port['port_descr_descr'];
        $bill_data['bill_ref'] = $port['port_descr_circuit'];
        $bill_data['bill_notes'] = $port['port_descr_speed'];
        $port_device_id = $port['device_id'];
    } ?>

 <div class="modal fade bs-example-modal-sm" id="create-bill" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="Create">Add Traffic Bill</h4>
        </div>
        <div class="modal-body">
            <form method="post" role="form" action="bills/" class="form-horizontal alerts-form">
                <?php echo csrf_field() ?>
                <input type="hidden" name="addbill" value="yes" />

                <div class="form-group">
                    <label class="col-sm-4 control-label" for="device">Device</label>
                    <div class="col-sm-8">
                        <select class="form-control input-sm" id="device" name="device" onchange="getInterfaceList(this)"></select>
                        <script type="text/javascript">
                            init_select2('#device', 'device', {}, <?php echo "{id: $port_device_id, text: '" . format_hostname($device) . "'}"; ?>, '', {dropdownParent: $('#create-bill .modal-content')});
                        </script>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="port_id">Port</label>
                    <div class="col-sm-8">
                        <select class="form-control input-sm" id="port_id" name="port_id">
                        <?php
                        if (is_array($port)) {
                            // Need to pre-populate port as we've got a port pre-selected
                            foreach (dbFetch('SELECT * FROM ports WHERE device_id = ?', [$port_device_id]) as $interface) {
                                $interface = cleanPort($interface);
                                $string = $interface['label'] . ' - ' . \LibreNMS\Util\Clean::html($interface['ifAlias'], []);
                                $selected = $interface['port_id'] === $port['port_id'] ? ' selected' : '';
                                echo "<option value='${interface['port_id']}' $selected>$string</option>\n";
                            }
                        } ?>
                        </select>
                    </div>
                </div>

    <?php
    if (Config::get('billing.95th_default_agg') == 1) {
        $bill_data['dir_95th'] = 'agg';
    } else {
        $bill_data['dir_95th'] = 'in';
    }
    $bill_data['bill_type'] = 'cdr';
    $quota = ['select_gb' => ' selected'];
    $cdr = ['select_mbps' => ' selected'];
    include 'includes/html/pages/bill/addoreditbill.inc.php'; ?>
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
