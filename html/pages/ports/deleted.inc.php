<?php

if($_GET['optb'] == "purge" && $_GET['optc'] == "all") { 

  $sql = "SELECT * FROM `ports` AS P, `devices` as D WHERE P.`deleted` = '1' AND D.device_id = P.device_id";
  $query = mysql_query($sql);
  while($interface = mysql_fetch_assoc($query)) {
    if(interfacepermitted($interface['interface_id'], $interface['device_id'])){
      mysql_query("DELETE FROM `ports` WHERE `interface_id` = '".$interface['interface_id']."'");
      if(mysql_affected_rows()) { echo("<div class=infobox>Deleted ".generatedevicelink($interface)." - ".generateiflink($interface)."</div>"); }
    }
  }
} elseif($_GET['optb'] == "purge" && $_GET['optc'])  { 
  $interface = mysql_fetch_assoc(mysql_query("SELECT * from `ports` AS P, `devices` AS D WHERE `interface_id` = '".mres($_GET['optc'])."' AND D.device_id = P.device_id"));
  if(interfacepermitted($interface['interface_id'], $interface['device_id']))
  mysql_query("DELETE FROM `ports` WHERE `interface_id` = '".mres($_GET['optc'])."' AND `deleted` = '1'");
  if(mysql_affected_rows()) { echo("<div class=infobox>Deleted ".generatedevicelink($interface)." - ".generateiflink($interface)."</div>"); }
}



$i_deleted = 1;


echo("<table cellpadding=5 cellspacing=0 border=0 width=100%>");
echo("<tr><td></td><td></td><td></td><td><a href='".$config['base_url'] . "/ports/deleted/purge/all/'><img src='images/16/cross.png' align=absmiddle></img> Purge All</a></td></tr>");

$sql = "SELECT * FROM `ports` AS P, `devices` as D WHERE P.`deleted` = '1' AND D.device_id = P.device_id";
$query = mysql_query($sql);
while($interface = mysql_fetch_assoc($query)) {
  $interface = ifLabel($interface, $interface);
  if(interfacepermitted($interface['interface_id'], $interface['device_id'])){
    if(is_integer($i_deleted/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    echo("<tr bgcolor=$row_colour>");
     echo("<td width=250>".generatedevicelink($interface)."</td>");
     echo("<td width=250>".generateiflink($interface)."</td>");
     echo("<td></td>");
     echo("<td width=100><a href='".$config['base_url'] . "/ports/deleted/purge/".$interface['interface_id']."/'><img src='images/16/cross.png' align=absmiddle></img> Purge</a></td>");

    $i_deleted++;
  }
}

echo("</table>");


?>
