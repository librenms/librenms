<?php

mysql_query("ALTER TABLE `eventlog` DROP `id`");
mysql_query("ALTER TABLE `eventlog` ADD  `event_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY  FIRST");

$s = "SELECT * FROM eventlog";
$q = mysql_query($s);
while ($event = mysql_fetch_assoc($q))
{
  if ($event['interface'])
  {
    mysql_query("UPDATE `eventlog` SET `interface` = NULL, `type` = 'interface', `reference` = '".$event['interface']."' WHERE `event_id` = '".$event['event_id']."'");
  }

  $i++;
}

mysql_query("ALTER TABLE `eventlog` DROP `interface`");

?>