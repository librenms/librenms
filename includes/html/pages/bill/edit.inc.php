<?php

// Don't refresh this page to stop adding multiple ports
$no_refresh = true;

  require 'includes/html/javascript-interfacepicker.inc.php';

  // This needs more verification. Is it already added? Does it exist?
  // Calculation to extract MB/GB/TB of Kbps/Mbps/Gbps
$base = \LibreNMS\Config::get('billing.base');

if ($bill_data['bill_type'] == 'quota') {
    $data = $bill_data['bill_quota'];
    $tmp['mb'] = ($data / $base / $base);
    $tmp['gb'] = ($data / $base / $base / $base);
    $tmp['tb'] = ($data / $base / $base / $base / $base);
    if ($tmp['tb'] >= 1) {
        $quota = [
            'type'      => 'tb',
            'select_tb' => ' selected',
            'data'      => $tmp['tb'],
        ];
    } elseif (($tmp['gb'] >= 1) and ($tmp['gb'] < $base)) {
        $quota = [
            'type'      => 'gb',
            'select_gb' => ' selected',
            'data'      => $tmp['gb'],
        ];
    } elseif (($tmp['mb'] >= 1) and ($tmp['mb'] < $base)) {
        $quota = [
            'type'      => 'mb',
            'select_mb' => ' selected',
            'data'      => $tmp['mb'],
        ];
    }
}//end if

if ($bill_data['bill_type'] == 'cdr') {
    $data = $bill_data['bill_cdr'];
    $tmp['kbps'] = ($data / $base);
    $tmp['mbps'] = ($data / $base / $base);
    $tmp['gbps'] = ($data / $base / $base / $base);
    if ($tmp['gbps'] >= 1) {
        $cdr = [
            'type'        => 'gbps',
            'select_gbps' => ' selected',
            'data'        => $tmp['gbps'],
        ];
    } elseif (($tmp['mbps'] >= 1) and ($tmp['mbps'] < $base)) {
        $cdr = [
            'type'        => 'mbps',
            'select_mbps' => ' selected',
            'data'        => $tmp['mbps'],
        ];
    } elseif (($tmp['kbps'] >= 1) and ($tmp['kbps'] < $base)) {
        $cdr = [
            'type'        => 'kbps',
            'select_kbps' => ' selected',
            'data'        => $tmp['kbps'],
        ];
    }
}//end if
?>
<div class="row">
<div class="col-lg-6 col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Bill Properties</h3>
        </div>
        <div class="panel-body">
            <form id="edit" name="edit" method="post" action="" class="form-horizontal" role="form">
                <?php echo csrf_field() ?>
              <input type=hidden name="action" value="update_bill">
              <script type="text/javascript">
                function billType() {
                    $('#cdrDiv').toggle();
                    $('#quotaDiv').toggle();
                }
              </script>
                <?php   include 'includes/html/pages/bill/addoreditbill.inc.php'; ?>
                <div class="form-group">
                  <div class="col-sm-offset-4 col-sm-4">
                    <button type="submit" class="btn btn-primary" name="Submit" value="Save" /><i class="fa fa-check"></i> Save Properties</button>
                  </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="col-lg-6 col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Billed Ports</h3>
        </div>
        <div class="panel-body">
        <div class="form-group">
            <?php
            //This needs a proper cleanup
            $ports = dbFetchRows(
                'SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D
                WHERE B.bill_id = ? AND P.port_id = B.port_id
                AND D.device_id = P.device_id ORDER BY D.device_id',
                [$bill_data['bill_id']]
            );

            if (is_array($ports)) {
                ?>
            <div class="list-group">
                <?php   foreach ($ports as $port) {
                    $port = cleanPort($port);
                    $emptyCheck = true;
                    $portalias = (empty($port['ifAlias']) ? '' : ' - ' . $port['ifAlias'] . ''); ?>
                <div class="list-group-item">
                    <form action="" class="form-inline" method="post" name="delete<?php echo $port['port_id'] ?>" style="display: none;">
                        <?php echo csrf_field() ?>
                        <input type="hidden" name="action" value="delete_bill_port" />
                        <input type="hidden" name="port_id" value="<?php echo $port['port_id'] ?>" />
                    </form>
                    
                    <button class="btn btn-danger btn-xs pull-right" onclick="if (confirm('Are you sure you wish to remove this port?')) { document.forms['delete<?php echo $port['port_id'] ?>'].submit(); }">
                        <i class="fa fa-minus"></i>
                        Remove Interface
                    </button>
                    <?php echo generate_device_link($port); ?>
                    <i class="fa fa-random"></i>
                    <?php echo generate_port_link($port, $port['ifName'] . '' . $portalias); ?>
                </div>
                <?php
                }
                if (! $emptyCheck) { ?>
                <div class="alert alert-info">There are no ports assigned to this bill</alert>
                <?php                   } ?>
            
            </div>
                
                <?php
            }
            $port_device_id = -1;
            ?>
        </div>

        <h4>Add Port</h4>
        
        <form action="" method="post" class="form-horizontal" role="form">
            <?php echo csrf_field() ?>
            <input type="hidden" name="action" value="add_bill_port" />
            <input type="hidden" name="bill_id" value="<?php echo $bill_id; ?>" />
            
            <div class="form-group">
                <label class="col-sm-2 control-label" for="device">Device</label>
                <div class="col-sm-8">
                    <select class="form-control input-sm" id="device" name="device" onchange="getInterfaceList(this)"></select>
                    <script type="text/javascript">
                        init_select2('#device', 'device', {}, <?php echo "{id: $port_device_id, text: '" . format_hostname($device) . "'}"; ?>);
                    </script>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="port_id">Port</label>
                <div class="col-sm-8">
                    <select class="form-control input-sm" id="port_id" name="port_id"></select>
                </div>
            </div>
            <div class="col-sm-2 col-sm-offset-2">
                <button type="submit" class="btn btn-primary" name="Submit" value=" Add " /><i class="fa fa-plus"></i> Add Port</button>
            </div>
        </form>
    </div>
</div>
