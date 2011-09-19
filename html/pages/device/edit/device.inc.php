<?php

if ($_POST['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    $updated = 0;

    $descr = mres($_POST['descr']);
    $ignore = mres($_POST['ignore']);
    $type = mres($_POST['type']);
    $disabled = mres($_POST['disabled']);

    $override_sysLocation_bool = mres($_POST['override_sysLocation']);
    if (isset($_POST['sysLocation'])) { $override_sysLocation_string = mres($_POST['sysLocation']); }

    if (get_dev_attrib($device,'override_sysLocation_bool') != $override_sysLocation_bool
     || get_dev_attrib($device,'override_sysLocation_string') != $override_sysLocation_string)
    {
      $updated = 1;
    }

    if ($override_sysLocation_bool) { set_dev_attrib($device, 'override_sysLocation_bool', '1'); } else { del_dev_attrib($device, 'override_sysLocation_bool'); }
    if (isset($override_sysLocation_string)) { set_dev_attrib($device, 'override_sysLocation_string', $override_sysLocation_string); };

    #FIXME needs more sanity checking! and better feedback

    $param = array('purpose' => $_POST['descr'], 'type' => $_POST['type'], 'ignore' => $_POST['ignore'], 'disabled' => $_POST['disabled']);

    $rows_updated = dbUpdate($param, 'devices', '`device_id` = ?', array($device['device_id']));

    if ($rows_updated > 0 || $updated)
    {
      $update_message = "Device record updated.";
      $updated = 1;
      $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
    } elseif ($rows_updated = '-1') {
      $update_message = "Device record unchanged. No update necessary.";
      $updated = -1;
    } else {
      $update_message = "Device record update error.";
    }
  }
  else
  {
    include("includes/error-no-perm.inc.php");
  }
}

$descr  = $device['purpose'];

function foldersize($path) {
    $total_size = 0;
    $files = scandir($path);
    $total_files = 0;
    foreach($files as $t) {
        if (is_dir(rtrim($path, '/') . '/' . $t)) {
            if ($t<>"." && $t<>"..") {
                $size = foldersize(rtrim($path, '/') . '/' . $t);
                $total_size += $size;
            }
        } else {
            $size = filesize(rtrim($path, '/') . '/' . $t);
            $total_size += $size;
            $total_files++;
        }
    }
    return array($total_size, $total_files);
}

$override_sysLocation_bool = get_dev_attrib($device,'override_sysLocation_bool');
$override_sysLocation_string = get_dev_attrib($device,'override_sysLocation_string');

list($sizeondisk, $numrrds) = foldersize($config['rrd_dir']."/".$device['hostname']);

echo("<b>Size on Disk:" . formatStorage($sizeondisk) . " in " . $numrrds . " RRD files.</b>");


if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

?>
<table cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <h5>
        <form id="delete_host" name="delete_host" method="post" action="<?php echo($config['base_url'].'/delhost/'); ?>">
          <img src="images/16/server_delete.png" align="absmiddle">
          <input type="hidden" name="id" value="<?php echo($device['device_id']); ?>">
          <input type="submit" class="submit" name="Submit" value="Delete Host">
        </form>
      </h5>

<form id="edit" name="edit" method="post" action="">
  <input type=hidden name="editing" value="yes">
  <table width="500" border="0">
    <tr>
      <td colspan="2" align="right">Description:</td>
      <td colspan="3"><input name="descr" size="32" value="<?php echo($device['purpose']); ?>"></input></td>
    </tr>
   <tr>
      <td colspan="2" align="right">
        Type:
      </td>
      <td>
        <select name="type">
<?php
$unknown = 1;

foreach ($config['device_types'] as $type)
{
  echo('          <option value="'.$type['type'].'"');
  if ($device['type'] == $type['type'])
  {
    echo(' selected="1"');
    $unknown = 0;
  }
  echo(' >' . ucfirst($type['type']) . '</option>');
}
  if ($unknown)
  {
    echo('          <option value="other">Other</option>');
  }
?>
        </select>
      </td>
    </tr>
    <tr>
      <td width="40"><div style="padding-right: 5px; text-align: right"><input onclick="edit.sysLocation.disabled=!edit.override_sysLocation.checked" type="checkbox" name="override_sysLocation"<?php if ($override_sysLocation_bool) { echo(' checked="1"'); } ?> /></div></td>
      <td width="150" align="right">Override sysLocation:</td>
      <td><input name="sysLocation" size="32"<?php if (!$override_sysLocation_bool) { echo(' disabled="1"'); } ?> value="<?php echo($override_sysLocation_string); ?>" /></td>
    </tr>
    <tr>
      <td colspan="2"><div align="right">Disable</div></td>
      <td><input name="disabled" type="checkbox" id="disabled" value="1" <?php if ($device["disabled"]) { echo("checked=checked"); } ?> /></td>
      <td><div align="right">Ignore</div></td>
      <td><input name="ignore" type="checkbox" id="disable" value="1" <?php if ($device['ignore']) { echo("checked=checked"); } ?> /></td>
    </tr>
  </table>
  <input type="submit" name="Submit" value="Save" />
  <label><br />
  </label>
</form>

</td>
<td width="50"></td><td></td></tr></table>
