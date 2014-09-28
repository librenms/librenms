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
  echo('
  <div class="row">
    <div class="col-sm-offset-2 col-sm-7">
');
  if ($_REQUEST['confirm'])
  {
    print_message(delete_device(mres($_REQUEST['id']))."\n");
  }
  else
  {
    $device = device_by_id_cache($_REQUEST['id']);
    print_error("Are you sure you want to delete device " . $device['hostname'] . "?");
?>
<br />
<form name="form1" method="post" action="" class="form-horizontal" role="form">
  <div class="form-group">
    <div class="col-sm-4">
    <input type="hidden" name="id" value="<?php echo $_REQUEST['id'] ?>" />
    <input type="hidden" name="confirm" value="1" />
    <!--<input type="hidden" name="remove_rrd" value="<?php echo $_POST['remove_rrd']; ?>">-->
      <button type="submit" class="btn btn-default">Confirm host deletion</button>
    </div>
  </div>
</form>
<?php
  }
  echo('
    </div>
  </div>
');
}
else
{
?>

<form name="form1" method="post" action="" class="form-horizontal" role="form">
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-7">
<?php print_error("Warning, this will remove the device from being monitered and if selected, all data will be removed as well!");?>
    </div>
  </div>
  <div class="form-group">
    <label for="id" class="col-sm-2 control-label">Device:</label>
    <div class="col-sm-7">
      <select name="id" class="form-control" id="id">

<?php

foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $data)
{
  echo("<option value='".$data['device_id']."'>".$data['hostname']."</option>");
}

?>
      </select>
    </div>
  </div>
  <div class="form-group">
 <!-- <tr>
    <td>Remove RRDs (Data files): </td>
    <td><input type="checkbox" name="remove_rrd" value="yes"></td>
  </tr>-->
   <div class="col-sm-offset-2 col-sm-8">
     <input id="confirm" type="hidden" name="confirm" value="0" />
     <button id="confirm_delete" type="submit" class="btn btn-default">Delete Device</button>
  </div>
</form>
<?php
}
?>
