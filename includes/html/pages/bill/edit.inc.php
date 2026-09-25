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
<?php print_source_list($bill, $bill_id) ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Add Source</h3>
    </div>
    <div class="panel-body">
        <form action="<?php echo route('bill.source.attach', $bill_id); ?>" method="post" class="form-horizontal" role="form">
            <?php echo csrf_field() ?>
            <?php
            $picker_label_cols = 2;
            $picker_config = '{}';
            include 'includes/html/pages/bill/source-picker.inc.php';
            ?>
            <div class="col-sm-2 col-sm-offset-2">
                <button type="submit" class="btn btn-primary" name="Submit" value=" Add "><i class="fa fa-plus"></i> Add Source</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
