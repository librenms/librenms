<?php

if ($_POST['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    $descr = mres($_POST['descr']);
    $ignore = mres($_POST['ignore']);
    $type = mres($_POST['type']);
    $disabled = mres($_POST['disabled']);

    #FIXME needs more sanity checking! and better feedback
    $sql = "UPDATE `devices` SET `purpose` = '" . $descr . "', `type` = '$type'";
    $sql .= ", `ignore` = '$ignore',  `disabled` = '$disabled'";
    $sql .= " WHERE `device_id` = '".$device['device_id']."'";
    $query = mysql_query($sql);

    $rows_updated = mysql_affected_rows();

    if ($rows_updated > 0)
    {
      $update_message = mysql_affected_rows() . " Device record updated.";
      $updated = 1;
    } elseif ($rows_updated = '-1') {
      $update_message = "Device record unchanged. No update necessary.";
      $updated = -1;
    } else {
      $update_message = "Device record update error.";
      $updated = 0;
    }
  }
}

$device = mysql_fetch_assoc(mysql_query("SELECT * FROM `devices` WHERE `device_id` = '".$device['device_id']."'"));
$descr  = $device['purpose'];

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

echo("<table cellpadding=0 cellspacing=0><tr><td>

<h5>
  <a href='?page=delhost&id=".$device['device_id']."'>
    <img src='images/16/server_delete.png' align='absmiddle'>
    Delete
  </a>
</h5>

<form id='edit' name='edit' method='post' action=''>
  <input type=hidden name='editing' value='yes'>
  <table width='400' border='0'>
    <tr>
      <td><div align='right'>Description</div></td>
      <td colspan='3'><input name='descr' size='32' value='" . $device['purpose'] . "'></input></td>
    </tr>
   <tr>
      <td align='right'>
        Type
      </td>
      <td>
        <select name='type'>");

$unknown = 1;
foreach ($device_types as $type)
{
  echo('          <option value="'.$type.'"');
  if ($device['type'] == $type)
  {
    echo('selected="1"');
    $unknown = 0;
  }
  echo(' >' . ucfirst($type) . '</option>');
}
  if ($unknown)
  {
    echo('          <option value="other">Other</option>');
  }
echo("
        </select>
      </td>
    </tr>
    <tr>
      <td><div align='right'>Disable</div></td>
      <td><input name='disabled' type='checkbox' id='disabled' value='1'");
if ($device['disabled']) { echo("checked=checked"); }
echo("/></td>
      <td><div align='right'>Ignore</div></td>
      <td><input name='ignore' type='checkbox' id='disable' value='1'");
      if ($device['ignore']) { echo("checked=checked"); }
echo("/></td>
    </tr>");

echo('
  </table>
  <input type="submit" name="Submit" value="Save" />
  <label><br />
  </label>
</form>

</td>
<td width="50"></td><td></td></tr></table>');

?>