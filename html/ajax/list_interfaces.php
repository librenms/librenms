<?php

  include("../../config.php");
  include("../../includes/functions.php");
  include("../includes/authenticate.inc");
  if(!$_SESSION['authenticated']) { echo("unauthenticated"); exit; }

if(isset($_GET['device_id'])){

$interfaces = mysql_query("SELECT * FROM interfaces WHERE device_id = '".$_GET['device_id']."'");
  while($interface = mysql_fetch_array($interfaces)) {
    echo "obj.options[obj.options.length] = new Option('".$interface['ifDescr']." - ".$interface['ifAlias']."','".$interface['interface_id']."');\n";
  }     
}

?> 
