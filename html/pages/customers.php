<?php

  $sql  = "SELECT * FROM `interfaces` AS I, `devices` AS D";
  $sql .= " WHERE `ifAlias` like 'Cust: %' AND I.device_id = D.device_id AND D.hostname LIKE '%" . $config['mydomain'] . "' ORDER BY I.ifAlias";
  $query = mysql_query($sql);

  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }

  echo("<table border=0 cellspacing=0 cellpadding=2 class=devicetable width=100%>");

  while($interface = mysql_fetch_array($query)) {
    $device    = &$interface;

    unset($class);

    $ifname = fixifname($device['ifDescr']);

    $interface['ifAlias'] = str_replace("Cust: ", "", $interface['ifAlias']);
    $interface['ifAlias'] = str_replace("[PNI]", "Private", $interface['ifAlias']);

    $ifclass = ifclass($interface['ifOperStatus'], $interface['ifAdminStatus']);
    
    $displayifalias = $device['ifAlias'];
    $device['ifAlias'] = str_replace(" [","|",$device['ifAlias']);
    $device['ifAlias'] = str_replace("] (","|",$device['ifAlias']);
    $device['ifAlias'] = str_replace(" (","||",$device['ifAlias']);
    $device['ifAlias'] = str_replace("]","|",$device['ifAlias']);
    $device['ifAlias'] = str_replace(")","|",$device['ifAlias']);
    list($device['ifAlias'],$class,$notes) = explode("|", $device['ifAlias']);
    $useifalias = $device['ifAlias'];
    $used = '1';
    if ($device['ifAlias'] == $previfalias) { unset($useifalias );
    } elseif ($previfalias) { 
     echo("<tr bgcolor='#ffffff' height='5'><td></td></tr>"); 
     if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
    }
    $previfalias = $device['ifAlias'];

    echo("
           <tr bgcolor='$bg'>
             <td width='7'></td>
             <td width='250'><span style='font-weight: bold;' class=interface>$useifalias</span></td>
             <td width='200'>" . generatedevicelink($device) . "</td>
             <td width='100'>" . generateiflink($interface, makeshortif($interface['ifDescr'])) . "</td>
             <td>$notes</td>
           </tr>
         ");

  }

  echo("</table>");

?>
