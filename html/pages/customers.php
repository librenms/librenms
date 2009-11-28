<?php

  $sql  = "SELECT * FROM `interfaces` AS I, `devices` AS D";
  $sql .= " WHERE I.ifAlias like 'Cust: %' AND I.device_id = D.device_id  ORDER BY I.ifAlias,D.hostname";
  $query = mysql_query($sql);

  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }

  echo("<table border=0 cellspacing=0 cellpadding=2 class=devicetable width=100%>");

    echo("
           <tr bgcolor='$list_colour_a'>
             <th width='7'></th>
             <th width='250'><span style='font-weight: bold;' class=interface>Customer</span></th>
             <th width='150'>Device</th>
             <th width='100'>Interface</th>
             <th width='100'>Speed</th>
             <th width='100'>Circuit</th>
             <th>Notes</th>
           </tr>
         ");


  $customers = 1;
  while($interface = mysql_fetch_array($query)) {
    $device    = &$interface;

    unset($class);

    $ifname = fixifname($device['ifDescr']);

    $ifclass = ifclass($interface['ifOperStatus'], $interface['ifAdminStatus']);

    list(,$customer) = preg_split("/[\:\[\]\{\}\(\)]/", $interface['ifAlias']);  
    list(,$circuit) = preg_split("/[\{\}]/", $interface['ifAlias']);
    list(,$notes) = preg_split("/[\(\)]/", $interface['ifAlias']);
    list(,$speed) = preg_split("/[\[\]]/", $interface['ifAlias']);
    $customer = trim($customer);

    if ($customer == $prev_customer) { 
      unset($customer);
    } else { 
     if(isset($prev_customer)) {
       echo("<tr bgcolor='$bg_colour'><td></td><td colspan=6>
       <img src='".$config['base_url']."/graph.php?cust=".rawurlencode($prev_customer)."&type=customer_bits&from=$day&to=$now&width=215&height=100'>
       <img src='".$config['base_url']."/graph.php?cust=".rawurlencode($prev_customer)."&type=customer_bits&from=$week&to=$now&width=215&height=100'>
       <img src='".$config['base_url']."/graph.php?cust=".rawurlencode($prev_customer)."&type=customer_bits&from=$month&to=$now&width=215&height=100'>
       <img src='".$config['base_url']."/graph.php?cust=".rawurlencode($prev_customer)."&type=customer_bits&from=$year&to=$now&width=215&height=100'>
       </td></tr>");
     }

     if(is_integer($customers/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
     $customers++;
     $prev_customer = $customer;
    }

    if($device['os'] == "ios") {

      if($interface['ifTrunk']) { $vlan = "<span class=box-desc><span class=red>" . $interface['ifTrunk'] . "</span></span>";
      } elseif ($interface['ifVlan']) { $vlan = "<span class=box-desc><span class=blue>VLAN " . $interface['ifVlan'] . "</span></span>"; 
      } else { $vlan = ""; }

    }

    echo("
           <tr bgcolor='$bg_colour'>
             <td width='7'></td>
             <td width='250'><span style='font-weight: bold;' class=interface>$customer</span></td>
             <td width='150'>" . generatedevicelink($device) . "</td>
             <td width='100'>" . generateiflink($interface, makeshortif($interface['ifDescr'])) . "</td>
             <td width='100'>$speed</td>
             <td width='100'>$circuit</td>
             <td>$notes</td>
           </tr>
         ");

  }

  echo("</table>");

?>
