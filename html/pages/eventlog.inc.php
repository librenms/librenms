<?php

$no_refresh = true;

$param = array();

if ($vars['action'] == 'expunge' && $_SESSION['userlevel'] >= '10') {
    dbQuery('TRUNCATE TABLE `eventlog`');
    print_message('Event log truncated');
}

$pagetitle[] = 'Eventlog';

print_optionbar_start();

?>

<form method="post" action="" class="form-inline" role="form" id="result_form">
    <div class="form-group">
      <label>
        <strong>Device</strong>
      </label>
      <select name="device" id="device" class="form-control input-sm">
        <option value="">All Devices</option>
        <?php
        foreach (get_all_devices() as $hostname) {
            $device_id = getidbyname($hostname);
            if (device_permitted($device_id)) {
                echo "<option value='".$device_id."'";
                if ($device_id == $_POST['device']) {
                    echo 'selected';
                }

                echo '>'.$hostname.'</option>';
            }
        }
        ?>
      </select>
    </div>
    <div class="form-group">
        <label>
            <strong>Type: </strong>
        </label>
        <select name="eventtype" id="eventtype" class="form-control input-sm">
            <option value="">All types</option>
<?php

foreach (dbFetchColumn("SELECT `type` FROM `eventlog` GROUP BY `type`") as $type) {
    echo "<option value='$type'";
    if ($type === $_POST['eventtype']) {
        echo ' selected';
    }
    echo ">$type</option>";
}

?>
        </select>
    </div>
    <button type="submit" class="btn btn-default input-sm">Filter</button>
</form>

<?php
print_optionbar_end();

require_once 'includes/common/eventlog.inc.php';
echo implode('', $common_output);

?>

