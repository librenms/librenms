<meta http-equiv="refresh" content="60">
<?php

$type = $_GET['id'];
$sql  = "select * FROM `devices` WHERE `status` = '0' OR `uptime` <= '3600' ORDER BY `hostname`";
$query = mysql_query($sql);

echo("<p class=page-header>$type</p>");
echo("<table cellspacing=5 border=0 bordercolor=#000000 cellpadding=2><tr>"); 

$i = 1;

while($data = mysql_fetch_array($query)) {

    unset($class); unset($flags);
    $id = $data[id];
    $hostname = $data[hostname];
    $os = $data[os];
    $uptime = $data[uptime];
    $hardware = $data[hardware];
    $version = $data[version];
    $location = $data[location];
    $status = $data[status];
    $hostname = str_replace(".enta.net","",$hostname);

    if($status == '0') { 
       $flags = "Unreachable"; 
       $class = "unreachable";
    } elseif ($uptime < '3600') { 
       $flags = "Rebooted"; 
       $class = "rebooted";
    }
    
    unset($colour);
    $hostname = makeshorthost($hostname);

    echo("<td width=150 align=center valign=middle class='$class'>
 	<a class=interface href='?page=device&id=$id'>$hostname</a><br /> 
        <span class=interface-desc>$flags </span><br />
        <span class=interface-desc><a href='?page=device&id=$id'>$hardware $os $version</a>
        <br/ ><a href='?page=locations&location=$location'>$location</a><br />
        ");
    if ($i < 5) { 
      $i++;
    } else { 
      $i=1;
      echo("</tr><tr>");
    }
  }
    
echo("</table>");
?>
