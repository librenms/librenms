<?php

// Don't refresh this page to stop adding multiple ports
$no_refresh = true;

  // This needs more verification. Is it already added? Does it exist?
  // Calculation to extract MB/GB/TB of Kbps/Mbps/Gbps
$base = \App\Facades\LibrenmsConfig::get('billing.base');

if ($bill_data['bill_type'] == 'quota') {
    $data = $bill_data['bill_quota'];
    $tmp['mb'] = ($data / $base / $base);
    $tmp['gb'] = ($data / $base / $base / $base);
    $tmp['tb'] = ($data / $base / $base / $base / $base);
    if ($tmp['tb'] >= 1) {
        $quota = [
            'type' => 'tb',
            'select_tb' => ' selected',
            'data' => $tmp['tb'],
        ];
    } elseif (($tmp['gb'] >= 1) and ($tmp['gb'] < $base)) {
        $quota = [
            'type' => 'gb',
            'select_gb' => ' selected',
            'data' => $tmp['gb'],
        ];
    } elseif (($tmp['mb'] >= 1) and ($tmp['mb'] < $base)) {
        $quota = [
            'type' => 'mb',
            'select_mb' => ' selected',
            'data' => $tmp['mb'],
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
            'type' => 'gbps',
            'select_gbps' => ' selected',
            'data' => $tmp['gbps'],
        ];
    } elseif (($tmp['mbps'] >= 1) and ($tmp['mbps'] < $base)) {
        $cdr = [
            'type' => 'mbps',
            'select_mbps' => ' selected',
            'data' => $tmp['mbps'],
        ];
    } elseif (($tmp['kbps'] >= 1) and ($tmp['kbps'] < $base)) {
        $cdr = [
            'type' => 'kbps',
            'select_kbps' => ' selected',
            'data' => $tmp['kbps'],
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
            <form id="edit" name="edit" method="post" action="<?php echo route('bill.update', $bill_id); ?>" class="form-horizontal" role="form">
                <?php echo csrf_field() ?>
                <?php echo method_field('PUT') ?>
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
            $ports = $bill->ports()->with('device')->orderBy('ports.device_id')->get()->map(fn ($port) => $port->toArray())->all();

            if (is_array($ports)) {
                ?>
            <div class="list-group">
                <?php   foreach ($ports as $port) {
                    $port = cleanPort($port);
                    $emptyCheck = true;
                    $portalias = (empty($port['ifAlias']) ? '' : ' - ' . $port['ifAlias'] . ''); ?>
                <div class="list-group-item">
                    <form action="<?php echo route('bill.port.detach', [$bill_id, $port['port_id']]); ?>" class="form-inline" method="post" name="delete<?php echo $port['port_id'] ?>" style="display: none;">
                        <?php echo csrf_field() ?>
                        <?php echo method_field('DELETE') ?>
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
                if (empty($emptyCheck)) { ?>
                <div class="alert alert-info">There are no ports assigned to this bill</alert>
                <?php                   } ?>

            </div>

                <?php
            }
            $port_device_id = -1;
            ?>
        </div>

        <h4>Add Port</h4>

        <form action="<?php echo route('bill.port.attach', $bill_id); ?>" method="post" class="form-horizontal" role="form">
            <?php echo csrf_field() ?>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="device">Device</label>
                <div class="col-sm-8">
                    <select class="form-control input-sm" id="device" name="device" onchange="billDeviceChanged()"></select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="port_id">Port</label>
                <div class="col-sm-8">
                    <select class="form-control input-sm" id="port_id" name="port_id"></select>
                </div>
            </div>
            <div class="col-sm-2 col-sm-offset-2">
                <button type="submit" class="btn btn-primary" name="Submit" value=" Add "><i class="fa fa-plus"></i> Add Port</button>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Billed SAPs</h3>
    </div>
    <div class="panel-body">
        <div class="form-group">
            <?php
            $bill_saps = $bill->mplsSaps()->with('device')->orderBy('mpls_saps.device_id')->get();

            if ($bill_saps->isNotEmpty()) {
                ?>
            <div class="list-group">
                <?php   foreach ($bill_saps as $bill_sap) {
                    $sapdescr = (empty($bill_sap->sapDescription) ? '' : ' - ' . htmlentities($bill_sap->sapDescription)); ?>
                <div class="list-group-item">
                    <form action="<?php echo route('bill.sap.detach', [$bill_id, $bill_sap->sap_id]); ?>" class="form-inline" method="post" name="deletesap<?php echo $bill_sap->sap_id ?>" style="display: none;">
                        <?php echo csrf_field() ?>
                        <?php echo method_field('DELETE') ?>
                    </form>

                    <button class="btn btn-danger btn-xs pull-right" onclick="if (confirm('Are you sure you wish to remove this SAP?')) { document.forms['deletesap<?php echo $bill_sap->sap_id ?>'].submit(); }">
                        <i class="fa fa-minus"></i>
                        Remove SAP
                    </button>
                    SAP <?php echo htmlentities($bill_sap->ifName . ':' . $bill_sap->encap_display) ?> (service <?php echo $bill_sap->svc_oid ?>)<?php echo $sapdescr ?>
                    on <?php echo \LibreNMS\Util\Url::deviceLink($bill_sap->device); ?>
                </div>
                <?php
                } ?>
            </div>
                <?php
            } else { ?>
            <div class="alert alert-info">There are no SAPs assigned to this bill</div>
                <?php
            } ?>
        </div>

        <h4>Add SAP</h4>

        <form action="<?php echo route('bill.sap.attach', $bill_id); ?>" method="post" class="form-horizontal" role="form">
            <?php echo csrf_field() ?>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="sap_device">Device</label>
                <div class="col-sm-8">
                    <select class="form-control input-sm" id="sap_device" name="sap_device" onchange="billSapDeviceChanged()"></select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="sap_id">SAP</label>
                <div class="col-sm-8">
                    <select class="form-control input-sm" id="sap_id" name="sap_id"></select>
                </div>
            </div>
            <div class="col-sm-2 col-sm-offset-2">
                <button type="submit" class="btn btn-primary" name="Submit" value=" Add "><i class="fa fa-plus"></i> Add SAP</button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    const makePortData = function (param) {
        param.device = $('#device').val();
        return param;
    }
    init_select2('#device', 'device', {}, 'Select Device');
    init_select2('#port_id', 'port', makePortData, 'Select Port');
    function billDeviceChanged() {
        $('#port_id').val(null).trigger('change'); // clear port selection
    }

    const makeSapData = function (param) {
        param.device = $('#sap_device').val();
        return param;
    }
    init_select2('#sap_device', 'device', {}, null, 'All Devices (optional filter)');
    init_select2('#sap_id', 'mpls-sap', makeSapData, null, 'Search SAP by port, service id or description');
    function billSapDeviceChanged() {
        $('#sap_id').val(null).trigger('change'); // clear SAP selection
    }
</script>
