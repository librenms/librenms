<?php

$no_refresh = true;

if ($_POST['addbill'] == 'yes') {
    if (! Auth::user()->hasGlobalAdmin()) {
        include 'includes/html/error-no-perm.inc.php';
        exit;
    }

    $updated = '1';

    if (isset($_POST['bill_quota']) or isset($_POST['bill_cdr'])) {
        if ($_POST['bill_type'] == 'quota') {
            if (isset($_POST['bill_quota_type'])) {
                if ($_POST['bill_quota_type'] == 'MB') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base'));
                }

                if ($_POST['bill_quota_type'] == 'GB') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base'));
                }

                if ($_POST['bill_quota_type'] == 'TB') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base'));
                }

                $bill_quota = (is_numeric($_POST['bill_quota']) ? $_POST['bill_quota'] * \LibreNMS\Config::get('billing.base') * $multiplier : 0);
                $bill_cdr = 0;
            }
        }

        if ($_POST['bill_type'] == 'cdr') {
            if (isset($_POST['bill_cdr_type'])) {
                if ($_POST['bill_cdr_type'] == 'Kbps') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base'));
                }

                if ($_POST['bill_cdr_type'] == 'Mbps') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base'));
                }

                if ($_POST['bill_cdr_type'] == 'Gbps') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base'));
                }

                $bill_cdr = (is_numeric($_POST['bill_cdr']) ? $_POST['bill_cdr'] * $multiplier : 0);
                $bill_quota = 0;
            }
        }
    }//end if

    $insert = [
        'bill_name'   => $_POST['bill_name'],
        'bill_type'   => $_POST['bill_type'],
        'bill_cdr'    => $bill_cdr,
        'bill_day'    => $_POST['bill_day'],
        'bill_quota'  => $bill_quota,
        'bill_custid' => $_POST['bill_custid'],
        'bill_ref'    => $_POST['bill_ref'],
        'bill_notes'  => $_POST['bill_notes'],
        'rate_95th_in'      => 0,
        'rate_95th_out'     => 0,
        'rate_95th'         => 0,
        'dir_95th'          => $_POST['dir_95th'],
        'total_data'        => 0,
        'total_data_in'     => 0,
        'total_data_out'    => 0,
        'rate_average'      => 0,
        'rate_average_in'   => 0,
        'rate_average_out'  => 0,
        'bill_last_calc'    => ['NOW()'],
        'bill_autoadded'    => 0,
    ];

    $bill_id = dbInsert($insert, 'bills');

    if (is_numeric($bill_id) && is_numeric($_POST['port_id'])) {
        dbInsert(['bill_id' => $bill_id, 'port_id' => $_POST['port_id']], 'bill_ports');
    }

    header('Location: ' . \LibreNMS\Util\Url::generate(['page' => 'bill', 'bill_id' => $bill_id, 'view' => 'edit']));
    exit();
}

$pagetitle[] = 'Billing';

echo "<meta http-equiv='refresh' content='10000'>";

include 'includes/html/modal/new_bill.inc.php';
?>
<div class="panel panel-default panel-condensed">
    <div class="table-responsive">
        <table class="table table-hover" id="bills-list">
        <thead>
            <th data-column-id="bill_name">Billing name</th>
            <th data-column-id="notes" data-sortable="false"></th>
            <th data-column-id="bill_type">Type</th>
            <th data-column-id="bill_allowed" data-align="right">Allowed</th>
            <th data-column-id="total_data_in" data-align="right">Inbound</th>
            <th data-column-id="total_data_out" data-align="right">Outbound</th>
            <th data-column-id="total_data" data-align="right">Total</th>
            <th data-column-id="rate_95th" data-align="right">95th Percentile</th>
            <th data-column-id="overusage" data-sortable="false" data-align="center">Overusage</th>
            <th data-column-id="predicted" data-sortable="false" data-align="center">Predicted</th>
            <th data-column-id="graph" data-sortable="false"></th>
            <th data-column-id="actions" data-sortable="false"></th>
        </thead>
        </table>
    </div>
</div>

<script type="text/html" id="table-header">
    <div id="{{ctx.id}}" class="{{css.header}}">
        <div class="row">
            <div class="col-sm-4">
            <?php if (Auth::user()->hasGlobalAdmin()) {  ?>
                <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#create-bill"><i class="fa fa-plus"></i> Create Bill</button>
            <?php } ?>
            </div>
            <div class="col-sm-8 actionBar">
                <span class="form-inline" id="table-filters">
                <fieldset class="form-group">
                    <select name='period' id='period' class="form-control input-sm">
                      <option value=''>Current Billing Period</option>
                      <option value='prev'>Previous Billing Period</option>
                    </select>
                    <select name='bill_type' id='bill_type' class="form-control input-sm">
                      <option value=''>All Types</option>
                      <option value='cdr'
                            <?php
                            if ($_GET['bill_type'] === 'cdr') {
                                echo 'selected';
                            }
                            ?>>CDR</option>
                      <option value='quota'
                            <?php
                            if ($_GET['bill_type'] === 'quota') {
                                echo 'selected';
                            }
                            ?>>Quota</option>
                    </select>
                    <select name='state' id='state' class="form-control input-sm">
                      <option value=''>All States</option>
                      <option value='under'
                            <?php
                            if ($_GET['state'] === 'under') {
                                echo 'selected';
                            }
                            ?>>Under Quota</option>
                      <option value='over'
                            <?php
                            if ($_GET['state'] === 'over') {
                                echo 'selected';
                            }
                            ?>>Over Quota</option>
                    </select>
                  </fieldset>
                </span>
                <p class="{{css.search}}"></p>
                <p class="{{css.actions}}"></p>
            </div>
        </div>
    </div>
</script>

<script type="text/javascript">
    var grid = $('#bills-list').bootgrid({
       ajax: true,
       templates: {
           header: $('#table-header').html()
       },
       columnSelection: false,
       rowCount: [50, 100, 250, -1],
       post: function() {
           return {
               id: 'bills',
               bill_type: $('select#bill_type').val(),
               state: $('select#state').val(),
               period: $('select#period').val()
           };
       },
       url: "ajax_table.php"
    }).on("loaded.rs.jquery.bootgrid", function() {
    });
    $('#table-filters select').on('change', function() { grid.bootgrid('reload'); });

<?php
if ($vars['view'] == 'add') {
                                ?>
$(function() {
    $('#create-bill').modal('show');
});
    <?php
                            }
?>
</script>
