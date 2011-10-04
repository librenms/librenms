<?php

if ($_SESSION['userlevel'] < '10')
{
  include("includes/error-no-perm.inc.php");
}
else
{
  if ($_POST['addsrv'])
  {
    if ($_SESSION['userlevel'] == '10')
    {
      include("includes/service-add.inc.php");
    }
  }

  if ($handle = opendir($config['install_dir'] . "/includes/services/"))
  {
    while (false !== ($file = readdir($handle)))
    {
      if ($file != "." && $file != ".." && !strstr($file, "."))
      {
        $servicesform .= "<option value='$file'>$file</option>";
      }
    }
    closedir($handle);
  }

  foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $device)
  {
    $devicesform .= "<option value='" . $device['device_id'] . "'>" . $device['hostname'] . "</option>";
  }

  if ($updated) { print_message("Device Settings Saved"); }

  echo("
<h4>Add Service</h4>
<form id='addsrv' name='addsrv' method='post' action=''>
  <input type=hidden name='addsrv' value='yes'>
  <table width='200' border='0'>
    <tr>
      <td>
        Device
      </td>
      <td>
        <select name='device'>
          $devicesform
        </select>
      </td>
    </tr>
    <tr>
      <td>
        Type
      </td>
      <td>
        <select name='type'>
          $servicesform
        </select>
      </td>
    </tr>
    <tr>
      <td width='300'><div align='right'>Description</div></td>
      <td colspan='2'><textarea name='descr' cols='50'></textarea></td>
    </tr>
    <tr>
      <td width='300'><div align='right'>IP Address</div></td>
      <td colspan='2'><input name='ip'></textarea></td>
    </tr>
    <tr>
      <td width='300'><div align='right'>Parameters</div></td>
      <td colspan='2'><input name='params'></textarea></td>
    </tr>
   <tr>
  </table>
  <input type='submit' name='Submit' value='Add' />
  <label><br />
  </label>
</form>");

}

?>
