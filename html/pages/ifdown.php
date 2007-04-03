<meta http-equiv="refresh" content="60">
<?php

$type = $_GET['id'];
$sql  = "select *, DATE_FORMAT(I.lastchange, '%l:%i%p %D %M %Y') AS changed, I.id as iid, I.snmpid 
as snmpid, D.id as did, D.hostname as hostname, I.if as ifname, I.name as ifalias ";
$sql .= "from interfaces as I, devices as D WHERE `up_admin` = 'up' AND `up` = 'down' AND I.host = D.id AND I.name NOT LIKE 'Test%'";
$sql .= "AND I.name NOT LIKE '%[eng]%' ORDER BY D.hostname, I.if ";
$query = mysql_query($sql);

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

    unset($colour);


     if($ifalias == "") { $ifalias = "*** Unlabeled ***"; }
 
	$ifalias = str_replace(" [","|",$ifalias);
        $ifalias = str_replace("] (","|",$ifalias);
        $ifalias = str_replace(" (","||",$ifalias);
        $ifalias = str_replace("]","|",$ifalias);
        $ifalias = str_replace(")","|",$ifalias);
        list($ifalias,$type,$notes) = explode("|", $ifalias);

     if( strpos($ifalias,':')) {
       list($class,$ifalias) = split(":",$ifalias,2);
     }
     if($class == "") { 
       if($ifalias == "*** Unlabelled ***") {
         $class = "unlabelled"; }
       else { 
         $class = "unknown"; } 
     }
     $ifname = makeshortif($ifname);
     $class = strtolower($class);

     echo("
     <table border=0 cellpadding=5 cellspacing=2>
       <tr>
         <td width=150 class=$class align=center valign=middle>
  	   <a class='interface' href='?page=interface&id=$iid'>$ifalias<br /> 
	   $type $notes</a><br />
           <span class=interface-desc><a href='?page=device&id=$did'>$hostname</a><br />
           <a href='?page=interface&id=$iid'>$ifname</a></span>
         </td>
       </tr>
     </table>
            ");

  }
    
?>
