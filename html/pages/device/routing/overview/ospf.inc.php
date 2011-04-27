<?php
echo("<pre>");
print_r(mysql_fetch_assoc(mysql_query("SELECT * FROM `ospf_instances` WHERE `device_id` = '".$device['device_id']."'")));
echo("</pre>");
?>
