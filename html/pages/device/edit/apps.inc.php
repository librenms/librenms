<?php

# Load our list of available applications
if ($handle = opendir($config['install_dir'] . "/includes/polling/applications/"))
{
  while (false !== ($file = readdir($handle)))
  {
    if ($file != "." && $file != ".." && strstr($file, ".inc.php"))
    {
      $applications[] = str_replace(".inc.php", "", $file);
    }
  }
  closedir($handle);
}

# Check if the form was POSTed
if ($_POST['device'])
{
  $updated = 0;

  foreach (array_keys($_POST) as $key)
  {
    if (substr($key,0,4) == 'app_')
    {
      $enabled[] = "'" . substr($key,4) . "'";
    }
  }
  
  $sql = "DELETE FROM applications WHERE device_id=" . $device['device_id'];
  if ($enabled)
  {
    $sql .= " AND app_type NOT IN (" . implode(',',$enabled) . ")";
  }
  mysql_query($sql);
  $updated += mysql_affected_rows();
  
  $sql = "SELECT app_type FROM applications WHERE device_id=" . $device['device_id'];
  $result = mysql_query($sql);
  while ($row = mysql_fetch_assoc($result))
  {
    $app_in_db[] = $row['app_type'];
  }

  foreach ($enabled as $app)
  {
    if (!in_array(trim($app,"'"),$app_in_db))
    {
      $sql = "INSERT INTO applications (device_id,app_type) VALUES (" . $device['device_id'] . ", " . $app . ")";
      mysql_query($sql);
      $updated += mysql_affected_rows();
    }
  }

  if ($updated)
  {
    print_message("Applications updated!");
  }
  else
  {
    print_message("No changes.");
  }
}

# Show list of apps with checkboxes
echo('<div style="padding: 10px;">');

if (mysql_result(mysql_query("SELECT COUNT(*) from `applications` WHERE `device_id` = '".$device['device_id']."'"), 0) > '0')
{
  $app_query = mysql_query("select * from applications WHERE device_id = '".$device['device_id']."' ORDER BY app_type");
  while ($application = mysql_fetch_assoc($app_query))
  {
    $app_enabled[] = $application['app_type'];
  }
}

echo("<div style='float: left; width: 100%'>
<form id='appedit' name='appedit' method='post' action=''>
  <input type=hidden name=device value='".$device['device_id']."'>
  <table cellpadding=3 cellspacing=0 width=100%>
    <tr align=center>
      <th width=100>Enable</th>
      <th align=left>Application</th>
    </tr>
");

$row = 1;

foreach ($applications as $app)
{
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("    <tr bgcolor=$row_colour>");
  echo("      <td align=center>");
  echo("        <input type=checkbox" . (in_array($app,$app_enabled) ? ' checked="1"' : '') . " name='app_". $app ."'>");
  echo("      </td>");
  echo("      <td align=left>". ucfirst($app) . "</td>");
  echo("    </tr>
");

  $row++;
}

echo('<tr><td></td><td><input type="submit" value="Save"></td></tr>');
echo('</table>');
echo('</form>');
echo('</div>');

?>