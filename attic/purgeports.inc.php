<?php

# FIXME there's a delete port function in the functions... merge?!
function delete_port($port_id)
{
   $ipaddrs = mysql_query("SELECT * FROM `ipaddr` WHERE `port_id` = '$port_id'");
   while ($ipaddr = mysql_fetch_assoc($ipaddrs))
   {
     echo("<div style='padding-left:8px; font-weight: normal;'>Deleting IPv4 address " . $ipaddr['addr'] . "/" . $ipaddr['cidr']);
     mysql_query("DELETE FROM addr WHERE id = '".$addr['id']."'");
     echo("</div>");
   }

   $ip6addr = mysql_query("SELECT * FROM `ip6addr` WHERE `port_id` = '$port_id'");
   while ($ip6addr = mysql_fetch_assoc($ip6addrs))
   {
     echo("<div style='padding-left:8px; font-weight: normal;'>Deleting IPv6 address " . $ip6addr['ip6_comp_addr'] . "/" . $ip6addr['ip6_prefixlen']);
     mysql_query("DELETE FROM ip6addr WHERE ip6_addr_id = '".$ip6addr['ip6_addr_id']."'");
     echo("</div>");
   }

   $ip6addr = mysql_query("SELECT * FROM `ip6addr` WHERE `port_id` = '$port_id'");
   while ($ip6addr = mysql_fetch_assoc($ip6addrs))
   {
     echo("<div style='padding-left:8px; font-weight: normal;'>Deleting IPv6 address " . $ip6addr['ip6_comp_addr'] . "/" . $ip6addr['ip6_prefixlen']);
     mysql_query("DELETE FROM ip6addr WHERE ip6_addr_id = '".$ip6addr['ip6_addr_id']."'");
     echo("</div>");
   }

   mysql_query("DELETE FROM `pseudowires` WHERE `port_id` = '$port_id'");
   mysql_query("DELETE FROM `mac_accounting` WHERE `port_id` = '$port_id'");
   mysql_query("DELETE FROM `links` WHERE `local_port_id` = '$port_id'");
   mysql_query("DELETE FROM `links` WHERE `remote_port_id` = '$port_id'");
   mysql_query("DELETE FROM `ports_perms` WHERE `port_id` = '$port_id'");
   mysql_query("DELETE FROM `ports` WHERE `port_id` = '$port_id'");
}

$ports = mysql_query("SELECT * FROM `ports` WHERE `deleted` = '1'");
while ($port = mysql_fetch_assoc($ports))
{
  echo("<div style='font-weight: bold;'>Deleting port " . $port['port_id'] . " - " . $port['ifDescr']);
  delete_port($port['port_id']);
  echo("</div>");
}

?>