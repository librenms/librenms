<?php

$no_refresh = true;

$param = array();

if ($vars['action'] == 'expunge' && $_SESSION['userlevel'] >= '10') {
    dbQuery('TRUNCATE TABLE `syslog`');
    print_message('syslog truncated');
}

$pagetitle[] = 'Syslog';

print_optionbar_start();

?>


<div id="{{ctx.id}}" class="{{css.header}}">
    <div class="row">
        <div class="col-sm-9 actionBar">
            <div class="pull-left">
                <form method="post" action="" class="form-inline" role="form" id="result_form">
                    <div class="form-group">
                        <select name="device" id="device" class="form-control input-sm">
                            <option value="">All Devices</option>
                                <?php
                                foreach (get_all_devices() as $hostname) {
                                    $device_id = getidbyname($hostname);
                                    if (device_permitted($device_id)) {
                                        echo '"<option value="'.$device_id.'"';
                                        if ($device_id == $vars['device']) {
                                            echo ' selected';
                                        }

                                        echo '>'.$hostname.'</option>';
                                    }
                                }
                                ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="program" id="program" class="form-control input-sm">
                            <option value="">All Programs</option>
                                <?php
                                foreach (dbFetchRows('SELECT DISTINCT `program` FROM `syslog` ORDER BY `program`') as $data) {
                                    echo '"<option value="'.$data['program'].'"';
                                    if ($data['program'] == $vars['program']) {
                                        echo ' selected';
                                    }

                                    echo '>'.$data['program'].'</option>';
                                }
                                ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="priority" id="priority" class="form-control input-sm">
                            <option value="">All Priorities</option>
                                <?php
                                foreach (dbFetchRows('SELECT DISTINCT `priority` FROM `syslog` ORDER BY `level`') as $data) {
                                    echo '"<option value="'.$data['priority'].'"';
                                    if ($data['priority'] == $vars['priority']) {
                                        echo ' selected';
                                    }

                                    echo '>'.$data['priority'].'</option>';
                                }
                                ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input name="from" type="text" class="form-control input-sm" id="dtpickerfrom" maxlength="16" value="<?php echo $vars['from']; ?>" placeholder="From" data-date-format="YYYY-MM-DD HH:mm">
                    </div>
                    <div class="form-group">
                        <input name="to" type="text" class="form-control input-sm" id="dtpickerto" maxlength="16" value="<?php echo $vars['to']; ?>" placeholder="To" data-date-format="YYYY-MM-DD HH:mm">
                    </div>
                    <button type="submit" class="btn btn-default input-sm">Filter</button>
                </form>
            </div>
        </div>
        <div class="col-sm-3 actionBar">
            <p class="{{css.actions}}"></p>
        </div>
    </div>
</div>

<script>
$(function () {
    $("#dtpickerfrom").datetimepicker();
    $("#dtpickerfrom").on("dp.change", function (e) {
        $("#dtpickerto").data("DateTimePicker").minDate(e.date);
    });
    $("#dtpickerto").datetimepicker();
    $("#dtpickerto").on("dp.change", function (e) {
        $("#dtpickerfrom").data("DateTimePicker").maxDate(e.date);
    });
    if( $("#dtpickerfrom").val() != "" ) {
        $("#dtpickerto").data("DateTimePicker").minDate($("#dtpickerfrom").val());
    }
    if( $("#dtpickerto").val() != "" ) {
        $("#dtpickerfrom").data("DateTimePicker").maxDate($("#dtpickerto").val());
    } else {
        $("#dtpickerto").data("DateTimePicker").maxDate('<?php echo date($config['dateformat']['byminute']); ?>');
    }
});
</script>

<?php
print_optionbar_end();
require_once 'includes/common/syslog.inc.php';
echo implode('',$common_output);
?>

