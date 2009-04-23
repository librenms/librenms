<?php

## ifDescr whitelist (used instead of ifName)
$config['ifdescr']['IOS'] = true;
$config['ifdescr']['IOS XE'] = true;


## AFI / SAFI pairs for BGP (and other stuff, perhaps)
$config['afi']['ipv4']['unicast']    = "IPv4";
$config['afi']['ipv4']['multiicast'] = "IPv4 Multicast";
$config['afi']['ipv4']['vpn']        = "VPNv4";
$config['afi']['ipv6']['unicast']    = "IPv6";
$config['afi']['ipv6']['multicast']  = "IPv6 Multicast";


##############################
# No changes below this line #
##############################

$config['version'] = "0.5.1";

$nagios_link = mysql_connect($config['nagios_db_host'], $config['nagios_db_user'], $config['nagios_db_pass']);
if (!$nagios_link) {
        echo "<h2>Nagios MySQL Error</h2>";
        die;
}
$nagios_db = mysql_select_db($config['nagios_db_name'], $nagios_link);

### Connect to database
$observer_link = mysql_connect($config['db_host'], $config['db_user'], $config['db_pass']);
if (!$observer_link) {
        echo "<h2>Observer MySQL Error</h2>";
        die;
}
$observer_db = mysql_select_db($config['db_name'], $observer_link);

# Set some times needed by loads of scripts (it's dynamic, so we do it here!)

$now = time();
$day = time() - (24 * 60 * 60);
$twoday = time() - (2 * 24 * 60 * 60);
$week = time() - (7 * 24 * 60 * 60);
$month = time() - (31 * 24 * 60 * 60);
$year = time() - (365 * 24 * 60 * 60);

?>
