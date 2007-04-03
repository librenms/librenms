<meta http-equiv="refresh" content="60">
<?php

$type = $_GET['id'];
$sql  = "select *, DATE_FORMAT(I.lastchange, '%l:%i%p %D %M %Y') AS changed, I.id as iid, I.snmpid as snmpid, D.id as did, D.hostname as hostname, I.if as ifname, I.name as ifalias ";
$sql .= "from interfaces as I, devices as D WHERE `up_admin` = 'up' AND `up` = 'down' AND I.host = D.id AND I.name NOT LIKE 'Test%'";
$sql .= "AND I.name NOT LIKE '%[eng]%' ORDER BY I.lastchange DESC";
$query = mysql_query($sql);

echo("<p class=page-header>$type</p>");

if($_GET['format'] == "rows") {
echo("<table cellspacing=0 border=0 cellpadding=2>");
echo("<tr class=interface bgcolor=#eeeeee>
        <td>Last Changed</td>
        <td width=5></td>
        <td>Hostname</td>
        <td width=5></td>
        <td>Interface</td>
        <td width=5></td>
        <td>Description</td>
        <td width=5></td>
        <td>Type</td>
        <td width=5></td>
        <td>Notes</td>
        </tr>");
} else {
#echo("<table cellspacing=5 cellpadding=3><tr>"); 
}
$i = 1;

  while($data = mysql_fetch_array($query)) {
    unset($class);
    $iid = $data[iid];
    $did = $data[did];
    $hostname = $data[hostname];
    $lastchange = $data[changed];
    $up = $data[up];
    $up_admin = $data[ip_admin];
    $ifname = fixifname($data[ifname]);
    $ifnamelong = $ifname;
    $hostnamelong = $data[hostname]; 
    $ifalias = $data[ifalias];

    $hostname = str_replace(".enta.net","",$hostname);

unset($colour);


     if($ifalias == "") { $ifalias = "* Unlabelled *"; }
 
	$ifalias = str_replace(" [","|",$ifalias);
        $ifalias = str_replace("] (","|",$ifalias);
        $ifalias = str_replace(" (","||",$ifalias);
        $ifalias = str_replace("]","|",$ifalias);
        $ifalias = str_replace(")","|",$ifalias);
        list($ifalias,$type,$notes) = explode("|", $ifalias);

     if( strpos($ifalias,': ')) {
       list($class,$ifalias) = split(": ",$ifalias,2);
     }
     if($class == "") { 
       if($ifalias == "* Unlabelled *") {
         $class = "unlabelled"; }
       else { 
         $class = "unknown"; } 
     }
     $class = $class . "cell";
     $ifname = makeshortif($ifname);
     $class = strtolower($class);

  if($_GET['format'] == "rows") {

  echo("<tr class=$class>
        <td>$lastchange</td>
        <td></td>
        <td><a href='?page=device&id=$did'>$hostnamelong</a></td>
        <td></td>
        <td><a href='?page=interface&id=$iid'>$ifnamelong</a></span></td>
        <td></td>
        <td><a href='?page=interface&id=$iid'>$ifalias</a></td>
        <td></td>
        <td>$type</td>
        <td></td>
        <td>$notes</td>
        </tr>");

  } else {


    echo("<div class=$class>
            <a href='?page=device&id=$did' class=device-head>$hostname</a><br />
            <a href='?page=interface&id=$iid' class=syslog>$ifname</a><br />
            <a href='?page=interface&id=$iid' class=interface-desc>$ifalias</a><br />
            <span class=interface-desc>$type<br />
            $notes
          </div>");


#     echo("<td width=150 class=$class align=center valign=middle>
#		<a class='interface' href='?page=interface&id=$iid'>$ifalias<br /> 
#		$type $notes</a><br />
#               <span class=interface-desc><a href='?page=device&id=$did'>$hostname</a><br />
#                <a href='?page=interface&id=$iid'>$ifname</a></span></td>
#            ");
#    if ($i < 6) { 
#      $i++;
#    } else { 
#      $i=1;
#      echo("</tr><tr>");
#    }
  }

  }
    
echo("</table>");
?>
