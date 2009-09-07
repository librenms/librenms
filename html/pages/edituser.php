<script type="text/javascript" src="<?php echo($config['base_url']); ?>/js/tw-sack.js"></script>
<script type="text/javascript">


var ajax = new Array();

function getInterfaceList(sel)
{
        var deviceId = sel.options[sel.selectedIndex].value;
        document.getElementById('interface_id').options.length = 0;     // Empty city select box
        if(deviceId.length>0){
                var index = ajax.length;
                ajax[index] = new sack();

                ajax[index].requestFile = '<?php echo($config['base_url']); ?>/ajax/list_interfaces.php?device_id='+deviceId;    // Specifying which file to get
                ajax[index].onCompletion = function(){ createInterfaces(index) };       // Specify function that will be executed after file has been found
                ajax[index].runAJAX();          // Execute AJAX function
        }
}

function createInterfaces(index)
{
        var obj = document.getElementById('interface_id');
        eval(ajax[index].response);     // Executing the response from Ajax as Javascript code
}

</script>

<?php

echo("<div style='margin: 10px;'>");

if($_SESSION['userlevel'] != '10') { include("includes/error-no-perm.inc.php"); } else {

if($_GET['user_id']) {
  $user_data = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = '" . $_GET['user_id'] . "'"));
    echo("<p><h2>" . $user_data['realname'] . "</h2><a href='?page=edituser'>Change...</a></p>");
  // Perform actions if requested

  if($_GET['action'] == "deldevperm") {
    mysql_query("DELETE FROM devices_perms WHERE `device_id` = '" . $_GET['device_id'] . "' AND `user_id` = '" . $_GET['user_id'] . "'");
  }
  if($_GET['action'] == "adddevperm") {
    mysql_query("INSERT INTO devices_perms (`device_id`, `user_id`) VALUES ('" . $_GET['device_id'] . "', '" . $_GET['user_id'] . "')");
  }

  if($_GET['action'] == "delifperm") {
    mysql_query("DELETE FROM interfaces_perms WHERE `interface_id` = '" . $_GET['interface_id'] . "' AND `user_id` = '" . $_GET['user_id'] . "'");
  }
  if($_GET['action'] == "addifperm") {
    mysql_query("INSERT INTO interfaces_perms (`interface_id`, `user_id`) VALUES ('" . $_GET['interface_id'] . "', '" . $_GET['user_id'] . "')");
  }

  if($_GET['action'] == "delbillperm") {
    mysql_query("DELETE FROM bill_perms WHERE `bill_id` = '" . $_GET['bill_id'] . "' AND `user_id` = '" . $_GET['user_id'] . "'");
  }
  if($_GET['action'] == "addbillperm") {
    mysql_query("INSERT INTO bill_perms (`bill_id`, `user_id`) VALUES ('" . $_GET['bill_id'] . "', '" . $_GET['user_id'] . "')");
  }




echo("<table width=100%><tr><td valign=top width=33%>");

  // Display devices this users has access to
  echo("<h3>Device Access</h3>");

  // Display devices this user doesn't have access to
  echo("<h4>Grant access to new device</h4>");
  echo("<form method='get' action=''>
          <input type='hidden' value='" . $_GET['user_id'] . "' name='user_id'>
          <input type='hidden' value='edituser' name='page'>
          <input type='hidden' value='adddevperm' name='action'>
          <select name='device_id' class=selector>");

  $device_list = mysql_query("SELECT * FROM `devices` ORDER BY hostname");
  while($device = mysql_fetch_array($device_list)) {
    unset($done);
    foreach($access_list as $ac) { if($ac == $device['device_id']) { $done = 1; } }
    if(!$done) {
      echo("<option value='" . $device['device_id'] . "'>" . $device['hostname'] . "</option>");
    }
  }

  echo("</select> <input type='submit' name='Submit' value='Add'></form>");


  $device_perm_data = mysql_query("SELECT * from devices_perms as P, devices as D WHERE `user_id` = '" . $_GET['user_id'] . "' AND D.device_id = P.device_id");
  while($device_perm = mysql_fetch_array($device_perm_data)) {
    echo($device_perm['hostname'] . " <a href='?page=edituser&action=deldevperm&user_id=" . $_GET['user_id'] . "&device_id=" . $device_perm['device_id'] . "'><img src='images/16/cross.png' align=absmiddle border=0></a><br />");
    $access_list[] = $device_perm['device_id'];
    $permdone = "yes";
  }

  if(!$permdone) { echo("None Configured"); }

  echo("</td><td valign=top width=33%>");
  echo("<h3>Interface Access</h3>");

  $interface_perm_data = mysql_query("SELECT * from interfaces_perms as P, interfaces as I, devices as D WHERE `user_id` = '" . $_GET['user_id'] .
                                     "' AND I.interface_id = P.interface_id AND D.device_id = I.device_id");
  while($interface_perm = mysql_fetch_array($interface_perm_data)) {


   echo("<table><tr><td><strong>".$interface_perm['hostname']." - ".$interface_perm['ifDescr']."</strong><br />".
                "" . $interface_perm['ifAlias'] . "</td><td width=50>&nbsp;&nbsp;<a href='?page=edituser&action=delifperm&user_id=" . $_GET['user_id'] .
     "&interface_id=" . $interface_perm['interface_id'] . "'><img src='images/16/cross.png' align=absmiddle border=0></a></td></tr></table>");
    $ipermdone = "yes";
  }

  if(!$ipermdone) { echo("None Configured"); }

  // Display devices this user doesn't have access to
  echo("<h4>Grant access to new interface</h4>");

  echo("<form action='' method='get'>
      <input type='hidden' value='" . $_GET['user_id'] . "' name='user_id'>
      <input type='hidden' value='edituser' name='page'>
      <input type='hidden' value='addifperm' name='action'>
      <table><tr><td>Device: </td>
       <td><select id='device' class='selector' name='device' onchange='getInterfaceList(this)'>
        <option value=''>Select a device</option>");

  $device_list = mysql_query("SELECT * FROM `devices` ORDER BY hostname");
  while($device = mysql_fetch_array($device_list)) {
    unset($done);
    foreach($access_list as $ac) { if($ac == $device['device_id']) { $done = 1; } }
    if(!$done) { echo("<option value='" . $device['device_id']  . "'>" . $device['hostname'] . "</option>"); }
  }


  echo("</select></td></tr><tr>
     <td>Interface: </td><td><select class=selector id='interface_id' name='interface_id'>
     </select></td>
     </tr><tr></table><input type='submit' name='Submit' value='Add'></form>");

  echo("</td><td valign=top width=33%>");
  echo("<h3>Bill Access</h3>");

  $bill_perm_data = mysql_query("SELECT * from bills AS B, bill_perms AS P WHERE P.user_id = '" . $_GET['user_id'] .
                                     "' AND P.bill_id = B.bill_id");

  while($bill_perm = mysql_fetch_array($bill_perm_data)) {
   echo("<table><tr><td><strong>".$bill_perm['bill_name']."</strong></td><td width=50>&nbsp;&nbsp;<a href='?page=edituser&action=delifperm&user_id=" .
    $_GET['user_id'] . "&interface_id=" . $bill_perm['interface_id'] . "'><img src='images/16/cross.png' align=absmiddle border=0></a></td></tr></table>");
    $bill_access_list[] = $bill_perm['bill_id'];

    $bpermdone = "yes";
  }

  if(!$bpermdone) { echo("None Configured"); }

  // Display devices this user doesn't have access to
  echo("<h4>Grant access to new bill</h4>");
  echo("<form method='get' action=''>
          <input type='hidden' value='" . $_GET['user_id'] . "' name='user_id'>
          <input type='hidden' value='edituser' name='page'>
          <input type='hidden' value='addbillperm' name='action'>
          <select name='bill_id' class=selector>");

  $bills = mysql_query("SELECT * FROM `bills` ORDER BY `bill_name`");
  while($bill = mysql_fetch_array($bills)) {
    unset($done);
    foreach($bill_access_list as $ac) { if($ac == $bill['bill_id']) { $done = 1; } }
    if(!$done) {
      echo("<option value='" . $bill['bill_id'] . "'>" . $bill['bill_name'] . "</option>");
    }
  }

echo("</select> <input type='submit' name='Submit' value='Add'></form>");

echo("</td></table>");

} else {

  $user_list = mysql_query("SELECT * FROM `users`");

  echo("<h3>Select a user to edit</h3>");

  echo("<form method='get' action=''>
          <input type='hidden' value='edituser' name='page'>
          <select name='user_id'>");
  while($user_entry = mysql_fetch_array($user_list)) {
    echo("<option value='" . $user_entry['user_id']  . "'>" . $user_entry['username'] . "</option>");
  }
  echo("</select><input type='submit' name='Submit' value='Select'></form>");



}

}

echo("</div>");

?>

