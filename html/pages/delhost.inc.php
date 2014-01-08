<h1>Delete Host</h1>

<?php

if ($_SESSION['userlevel'] < 10)
{
  include("includes/error-no-perm.inc.php");

  exit;
}

$pagetitle[] = "Delete device";

if (is_numeric($_REQUEST['id']))
{
  if ($_REQUEST['confirm'])
  {
    print_message(delete_device(mres($_REQUEST['id'])));
  }
  else
  {
    $device = device_by_id_cache($_REQUEST['id']);
    print_error("Are you sure you want to delete device " . $device['hostname'] . "?");
?>
<br />
<form name="form1" method="post" action="">
    <input type="hidden" name="id" value="<?php echo $_REQUEST['id'] ?>" />
    <input type="hidden" name="confirm" value="1" />
    <!--<input type="hidden" name="remove_rrd" value="<?php echo $_POST['remove_rrd']; ?>">-->
    <input type="submit" class="submit" name="Submit" value="Confirm host deletion" />

<?php
  }
}
else
{
?>

<form name="form1" method="post" action="">
<?php print_error("Warning, this will remove the device from being monitered and if selected, all data will be removed as well!");?>
<table border="0">
  <tr>
    <td>Device: </td>
    <td><select name="id">

<?php

foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $data)
{
  echo("<option value='".$data['device_id']."'>".$data['hostname']."</option>");
}

?>
    </select></td>
  </tr>
 <!-- <tr>
    <td>Remove RRDs (Data files): </td>
    <td><input type="checkbox" name="remove_rrd" value="yes"></td>
  </tr>-->
  <tr>
    <td colspan="2"><input id="confirm" type="hidden" name="confirm" value="0" />
    <input id="confirm_delete" type="submit" class="submit" name="Submit" value="Delete Device" /></td>
  </tr>
</table>
</form>
<?php
}
?>
