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
        <select name="type" id="type" class="form-control input-sm">
            <option value="">All types</option>
<?php

foreach (dbFetchRows("SELECT `type` FROM `eventlog` GROUP BY `type`") as $types) {
    echo '<option value="'.$types['type'].'"';
    if ($types['type'] === $_POST['type']) {
        echo ' selected';
    }
    echo '>'.$types['type'].'</option>';
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

