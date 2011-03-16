<?php

echo('<div style="padding: 10px;">');

if($_POST['addapp']) {
  if($_SESSION['userlevel'] == '10') {
    include("includes/app-add.inc.php");
  }
}

if($_POST['delapp']) {
  if($_SESSION['userlevel'] == '10') {
    include("includes/app-delete.inc.php");
  }
}

if ($handle = opendir($config['install_dir'] . "/includes/polling/applications/")) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && strstr($file, ".inc.php")) {
	    $file = str_replace(".inc.php", "", $file);
            $applicationsform .= "<option value='$file'>$file</option>";
        }
    }
    closedir($handle);
}

$query = mysql_query("SELECT * FROM `devices` ORDER BY `hostname`");
while($device = mysql_fetch_array($query)) {
  $devicesform .= "<option value='" . $device['device_id'] . "'>" . $device['hostname'] . "</option>";
}

if($updated) { print_message("Applications Updated"); }

if(mysql_result(mysql_query("SELECT COUNT(*) from `applications` WHERE `device_id` = '".$device['device_id']."'"), 0) > '0') {
   $i = "1";
   $app_query = mysql_query("select * from applications WHERE device_id = '".$device['device_id']."' ORDER BY app_type");
   while($application = mysql_fetch_array($app_query)) {
     $existform .= "<option value='" . $application['app_id'] . "'>" . $application['app_type'] . "</option>";
   }

}

if($existform){
echo('<div style="float: left;">');
echo("

<h1>Remove application</h1>

<form id='delapp' name='delapp' method='post' action=''>
  <input type=hidden name='delapp' value='yes'>
  <table width='200' border='0'>
        <option type=hidden name=device value='".$device['device_id']."'>
    <tr>
      <td>
        Type
      </td>
      <td>
        <select name='app'>
          $existform
        </select>
      </td>
    </tr>
  </table>
  <input type='submit' name='Submit' value='Delete' />
  <label><br />
  </label>
</form>");


echo('</div>');
}

echo('<div style="width: 45%; float: right;">');

echo("
<h1>Add application</h1>

<form id='addapp' name='addapp' method='post' action=''>
  <input type=hidden name='addapp' value='yes'>
  <table width='200' border='0'>
        <option type=hidden name=device value='".$device['device_id']."'>
    <tr>
      <td>
        Type
      </td>
      <td>
        <select name='type'>
          $applicationsform
        </select>
      </td>
    </tr>
  </table>
  <input type='submit' name='Submit' value='Add' />
  <label><br />
  </label>
</form>");

echo('</div>');

echo('</div>');

?>
