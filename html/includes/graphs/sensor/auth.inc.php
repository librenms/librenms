<?php

  $sensor = mysql_fetch_array(mysql_query("SELECT * FROM sensors WHERE sensor_id = '".mres($_GET['id'])."'"));
  $device = device_by_id_cache($sensor['device_id']);

  ## FIXME WE ACTUALLY NEED TO AUTH, OK?

?>
