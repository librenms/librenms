<?php

$sql = "SELECT * FROM `applications` WHERE `device_id`  = '".$device['device_id']."'";
if($debug) { echo($sql."\n"); }
$app_data = mysql_query($sql);

if(mysql_affected_rows()) 
{
  echo('Applications: ');
  while($app = mysql_fetch_array($app_data)) {
    $app_include = $config['install_dir'].'/includes/polling/applications/'.$app['app_type'].'.inc.php';
    if(is_file($app_include))  
    {
      include($app_include);
    } 
    else
    {
      echo($app['app_type'].' include missing! ');
    }
  }
  echo("\n");
}


?>
