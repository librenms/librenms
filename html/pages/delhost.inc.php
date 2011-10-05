<h1>Delete Host</h1>

<?php

if ($_SESSION['userlevel'] < 10)
{
  include("includes/error-no-perm.inc.php");
  exit;
}

if (is_numeric($_REQUEST['id']))
{
  if ($_REQUEST['confirm'])
  {
    print_message(delete_device(mres($_REQUEST['id'])));
  }
  else
  {
    $device = device_by_id_cache($_REQUEST['id']);
    print_message("Are you sure you want to delete device " . $device['hostname'] . "?");
?>
<br />
<form name="form1" method="post" action="">
    <input type="hidden" name="id" value="<?php echo $_REQUEST['id'] ?>" />
    <input type="hidden" name="confirm" value="1" />
    <input type="submit" class="submit" name="Submit" value="Confirm host deletion" />

<?php
  }
}
else
{
?>

<form name="form1" method="post" action="">
  <p><select name="id">

<?php

foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $data)
{
  echo("<option value='".$data['device_id']."'>".$data['hostname']."</option>");
}

?>
    </select>
    <input type="hidden" name="confirm" value="1" />
    <input type="submit" class="submit" name="Submit" value="Delete Host" />
</p>
</form>
<?php
}
?>
