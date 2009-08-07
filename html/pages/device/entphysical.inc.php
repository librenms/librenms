<?php

function printEntPhysical($ent, $level, $class) {

     global $device;

     $query = mysql_query("SELECT * FROM `entPhysical` WHERE device_id = '".$_GET['id']."' AND entPhysicalContainedIn = '".$ent."' ORDER BY entPhysicalContainedIn,entPhysicalIndex");
     while($ent = mysql_fetch_array($query)) {
       echo("
    <li class='$class'>");

       if($ent['entPhysicalClass'] == "chassis") { echo("<img src='images/16/server.png' style='vertical-align:middle'/> "); }
       if($ent['entPhysicalClass'] == "module") { echo("<img src='images/16/drive.png' style='vertical-align:middle'/> "); }
       if($ent['entPhysicalClass'] == "port") { echo("<img src='images/16/connect.png' style='vertical-align:middle'/> "); }
       if($ent['entPhysicalClass'] == "container") { echo("<img src='images/16/box.png' style='vertical-align:middle'/> "); }
       if($ent['entPhysicalClass'] == "sensor") { 
         echo("<img src='images/16/contrast.png' style='vertical-align:middle'/> "); 
         $link = " href='".$config['base_url'] . "/device/".$device['device_id']."/ciscosensors/".$ent['entSensorType']."/' onmouseover=\"return overlib('<img src=\'graph.php?host=49&type=cisco_entity_sensor&from=-2d&to=now&width=400&height=150&a=".$ent['entPhysical_id']."\'><img src=\'graph.php?host=49&type=cisco_entity_sensor&from=-2w&to=now&width=400&height=150&a=".$ent['entPhysical_id']."\'>', LEFT,FGCOLOR,'#e5e5e5', BGCOLOR, '#c0c0c0', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#050505');\" onmouseout=\"return nd();\"";
       } else { unset ($link); }
       if($ent['entPhysicalClass'] == "backplane") { echo("<img src='images/16/brick.png' style='vertical-align:middle'/> "); }
       if($ent['entPhysicalParentRelPos'] > '-1') {echo("<strong>".$ent['entPhysicalParentRelPos'].".</strong> ");}

       if($link) {echo("<a $link>");}

       if($ent['ifIndex']) {
         $interface = mysql_fetch_array(mysql_query("SELECT * FROM `interfaces` WHERE ifIndex = '".$ent['ifIndex']."' AND device_id = '".$device['device_id']."'"));
         $ent['entPhysicalName'] = generateiflink($interface);
                }

       if ($ent['entPhysicalModelName'] && $ent['entPhysicalName']) {
         echo("<strong>".$ent['entPhysicalModelName']  . "</strong> (".$ent['entPhysicalName'].")");
       } elseif($ent['entPhysicalModelName']) {
         echo("<strong>".$ent['entPhysicalModelName']  . "</strong>");
       } elseif($ent['entPhysicalName']) {
         echo("<strong>".$ent['entPhysicalName']."</strong>");
       } elseif($ent['entPhysicalDescr']) {
         echo("<strong>".$ent['entPhysicalDescr']."</strong>");
       }    

       if($ent['entPhysicalClass'] == "sensor") {
         echo(" (".$ent['entSensorValue'] ." ". $ent['entSensorType'].")");
       }

       echo("<br /><div class='interface-desc' style='margin-left: 20px;'>" . $ent['entPhysicalDescr']);

       if($link) {echo("</a>");}

       if($ent['entPhysicalSerialNum']) {
         echo(" <br /><span style='color: #000099;'>Serial No. ".$ent['entPhysicalSerialNum']."</span> ");
       }

       echo("</div>");

       $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `entPhysical` WHERE device_id = '".$_GET['id']."' AND entPhysicalContainedIn = '".$ent['entPhysicalIndex']."'"),0);
       if($count) {
         echo("<ul>");
         printEntPhysical($ent['entPhysicalIndex'], $level+1, '');
	 echo("</ul>");
       }
       echo("</li>");
    }
}

   echo("<div style='float: right;'>
           <a href='#' class='button' onClick=\"expandTree('enttree');return false;\"><img src='images/16/bullet_toggle_plus.png'>Expand All Nodes</a>
           <a href='#' class='button' onClick=\"collapseTree('enttree');return false;\"><img src='images/16/bullet_toggle_minus.png'>Collapse All Nodes</a>
         </div>");

   echo("<div style='clear: both;'><UL CLASS='mktree' id='enttree'>");
   $level = "0";
   $ent['entPhysicalIndex'] = "0";
   printEntPhysical($ent['entPhysicalIndex'], $level, "liOpen");
   echo("</ul></div>");


?>

