<?php

  $sql  = "select *, I.id as iid, I.ifIndex as ifIndex, D.id as did, D.hostname as hostname, I.if as ifname, I.name as ifalias ";
  $sql .= "from interfaces as I, devices as D WHERE `name` like 'Cust: %' AND I.host = D.id AND D.hostname LIKE '%" . $mydomain . "' ORDER BY I.name";
  $query = mysql_query($sql);

  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }

  echo("<table border=0 cellspacing=0 cellpadding=2 class=devicetable width=100%>");

  while($data = mysql_fetch_array($query)) {
    unset($class);
    $iid = $data['iid'];
    $ifIndex = $data['ifIndex'];
    $did = $data['did'];
    $device[id] = $did;
    $device['hostname'] = $data['hostname'];
    $hostname = $data['hostname'];
    $up = $data['up'];
    $up_admin = $data['up_admin'];
    $ifname = fixifname($data['ifname']);
    $ifalias = $data['ifalias'];
    $ifalias = str_replace("Cust: ", "", $ifalias);
    $ifalias = str_replace("[PNI]", "Private", $ifalias);
    $ifclass = ifclass($up, $up_admin);
    
    $displayifalias = $ifalias;
    $ifalias = str_replace(" [","|",$ifalias);
    $ifalias = str_replace("] (","|",$ifalias);
    $ifalias = str_replace(" (","||",$ifalias);
    $ifalias = str_replace("]","|",$ifalias);
    $ifalias = str_replace(")","|",$ifalias);
    list($ifalias,$class,$notes) = explode("|", $ifalias);
    $useifalias = $ifalias;
    $used = '1';
    if ($ifalias == $previfalias) { unset($useifalias );
    } elseif ($previfalias) { 
     echo("<tr bgcolor='#ffffff' height='5'><td></td></tr>"); 
     if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
    }
    $previfalias = $ifalias;

    $mouseoverint = "onmouseover=\"return overlib('<img src=\'graph.php?if=$iid&from=$twoday&to=$now&width=400&height=120&type=bits\'>');\"
                 onmouseout=\"return nd();\"";
    $mouseoverhost = "onmouseover=\"return overlib('<img src=\'graph.php?host=$did&from=$week&to=$now&width=400&height=120&type=cpu\'>');\"
                 onmouseout=\"return nd();\"";


    echo("
           <tr bgcolor='$bg'>
             <td width='7'></td>
             <td width='250'><span style='font-weight: bold;' class=interface>$useifalias</span></td>
             <td width='200'>" . generatedevicelink($device) . "</td>
             <td width='100'>" . generateiflink($data, makeshortif($data['if'])) . "</td>
             <td>$notes</td>
           </tr>
         ");

  }

  echo("</table>");

?>
