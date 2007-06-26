<?php 

  $service_alerts = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_status = '0'"),0);
  $if_alerts = mysql_result(mysql_query("SELECT count(*) FROM `interfaces` WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND `ignore` = '0'"),0);
  $device_alerts = "0"; 
  $device_alert_sql = "WHERE 0";

  $query_a = mysql_query("SELECT * FROM `devices`");
  while($device = mysql_fetch_array($query_a)) {
    if($device['status'] == 0 && $device['ignore'] == '0') { $this_alert = "1"; } elseif($device['ignore'] == '0') {
      if(mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_status = '0' AND service_host = '$device[id]'"),0)) { $this_alert = "1"; }
      if(mysql_result(mysql_query("SELECT count(*) FROM interfaces WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND device_id = '" . $device[device_id] . "'"),0)) { $this_alert = "1"; }
    }

    if($this_alert) { 
     $device_alerts++;
     $device_alert_sql .= " OR `device_id` = '" . $device['device_id'] . "'"; 
    }    
    unset($this_alert);
  }


?>

<div class="menu2">
<ul>
<li><a><img src='/images/16/lightbulb.png' border=0 align=absmiddle> Status
<!--[if IE 7]><!--></a><!--<![endif]-->
        <table><tr><td>
        <ul>
        <li><a href="?page=overview"><img src='/images/16/zoom.png' border=0 align=absmiddle> Overview</a></li>
        <li><a href="?page=eventlog"><img src='/images/16/report.png' border=0 align=absmiddle> Eventlog</a></li>
	<?php if($enable_syslog) {
  	  echo("<li><a href='?page=syslog'><img src='/images/16/page.png' border=0 align=absmiddle> Syslog</a></li>");
	} ?>
        <li><a href="?page=alerts"><img src='/images/16/exclamation.png' border=0 align=absmiddle> Alerts</a></li>
        </ul>
        </td></tr></table>
<!--[if lte IE 6]></a><![endif]-->

</li>
</ul>
<ul>
<li><a><img src='/images/16/server.png' border=0 align=absmiddle> Devices
<!--[if IE 7]><!--></a><!--<![endif]-->
        <table><tr><td>
        <ul>
        <li><a href='?page=devices'><img src='/images/16/server.png' border=0 align=absmiddle> All Devices</a></li>
<?php

  echo("
        <li><hr width=140 /></li>
        <li><a href='?page=devices&type=server'><img src='/images/16/server.png' border=0 align=absmiddle> Servers</a></li>
        <li><a href='?page=devices&type=network'><img src='/images/16/arrow_switch.png' border=0 align=absmiddle> Network</a></li>
        <li><a href='?page=devices&type=firewall'><img src='/images/16/shield.png' border=0 align=absmiddle> Firewalls</a></li>");

  echo("        <li><hr width=140 /></li>
        <li><a href='?page=devices&status=alerted'><img src='/images/16/server_error.png' border=0 align=absmiddle> Alerts ($device_alerts)</a></li>");

if($_SESSION['userlevel'] > '5') {
  echo("
        <li><hr width=140 /></li>
        <li><a href='?page=addhost'><img src='/images/16/server_add.png' border=0 align=absmiddle> Add Device</a></li>
        <li><a href='?page=delhost'><img src='/images/16/server_delete.png' border=0 align=absmiddle> Delete Device</a></li>");
}
?>

        </ul>
        </td></tr></table>
<!--[if lte IE 6]></a><![endif]-->
</li>
<li><a><img src='/images/16/cog.png' border=0 align=absmiddle> Services
<!--[if IE 7]><!--></a><!--<![endif]-->
        <table><tr><td>
        <ul>
        <li><a href="?page=services"><img src='/images/16/cog.png' border=0 align=absmiddle> All Services </a></li>
<?php if($service_alerts) { 
echo("  <li><hr width=140 /></li>
        <li><a href='?page=services&status=0'><img src='/images/16/cog_error.png' border=0 align=absmiddle> Alerts ($service_alerts)</a></li>"); 
} ?>

<?php
if($_SESSION['userlevel'] > '5') {
  echo("

        <li><hr width=140 /></li>
        <li><a href='?page=addsrv'><img src='/images/16/cog_add.png' border=0 align=absmiddle> Add Service</a></li>
        <li><a href='?page=delsrv'><img src='/images/16/cog_delete.png' border=0 align=absmiddle> Delete Service</a></li>");
}
?>
        </ul>
        </td></tr></table>
<!--[if lte IE 6]></a><![endif]-->
</li>
<li><a class="menu2four" href="?page=locations"><img src='/images/16/building.png' border=0 align=absmiddle> Locations</a></li>

<li><a><img src='/images/16/connect.png' border=0 align=absmiddle> Ports
<!--[if IE 7]><!--></a><!--<![endif]-->

<table><tr><td>
        <ul>


<li><a href='?page=interfaces'><img src='/images/16/connect.png' border=0 align=absmiddle> All Ports</a></li>

<?php

if($_SESSION['userlevel'] > '5') {
  echo("<li><hr width=140 /></li>");
  if($show_if_customers) { echo("<li><a href='?page=customers'><img src='/images/16/group_link.png' border=0 align=absmiddle> Customers</a></li>"); $ifbreak = 1;}
  if($show_if_transit) { echo("<li><a href='?page=iftype&type=transit'><img src='/images/16/world_link.png' border=0 align=absmiddle> Transit</a></li>");  $ifbreak = 1; }
  if($show_if_peering) { echo("<li><a href='?page=iftype&type=peering'><img src='/images/16/bug_link.png' border=0 align=absmiddle> Peering</a></li>"); $ifbreak = 1; }
  if($show_if_core) { echo("<li><a href='?page=iftype&type=core'><img src='/images/16/brick_link.png' border=0 align=absmiddle> Core</a></li>"); $ifbreak = 1;}
}

if($ifbreak && $interface_alerts) { echo("<li><hr width=140 /></li>"); }

if($interface_alerts) {
echo("<li><a href='?page=interfaces&status=0'><img src='/images/16/link_error.png' border=0 align=absmiddle> Alerts ($interface_alerts)</a></li>");
}

?>

</ul></td></tr></table>

<!--[if lte IE 6]></a><![endif]-->
</li>

<li><a class="menu2four" href="?page=temperatures"><img src='/images/16/weather_sun.png' border=0 align=absmiddle> Temperatures</a></li>

<li style='float: right;'><a><img src='/images/16/wrench.png' border=0 align=absmiddle> Configuration
<!--[if IE 7]><!--></a><!--<![endif]-->
    <table><tr><td>
    <ul>
    <li><a href="?page=preferences"><img src='/images/16/wrench_orange.png' border=0 align=absmiddle> My Settings</a></li>
    <li><a href="?page=preferences"><img src='/images/16/key.png' border=0 align=absmiddle> Change Password</a></li>
    <?php if($userlevel == '10') {
      echo("
        <li><hr /></li>
        <li><a href='?page=settings'><img src='/images/16/report.png' border=0 align=absmiddle> System Settings</a></li>
        <li><hr /></li>
	<li><a href='?page=adduser'><img src='/images/16/user_add.png' border=0 align=absmiddle> Add User</a></li>
        <li><a href='?page=deluser'><img src='/images/16/user_delete.png' border=0 align=absmiddle> Remove User</a></li>
        <li><a href='?page=edituser'><img src='/images/16/user_edit.png' border=0 align=absmiddle> Edit User</a></li>");              
    } ?>
    </ul>
    </td></tr></table>
<!--[if lte IE 6]></a><![endif]-->
</li>
</ul>

</div>
