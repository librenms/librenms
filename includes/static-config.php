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

### Connect to database
if (!@mysql_connect($config['db_host'], $config['db_user'], $config['db_pass'])) {
        echo "<h2>MySQL Error</h2>";
        die;
}
mysql_select_db($config['db_name']);

# Set some times needed by loads of scripts (it's dynamic, so we do it here!)

$now = time();
$day = time() - (24 * 60 * 60);
$twoday = time() - (2 * 24 * 60 * 60);
$week = time() - (7 * 24 * 60 * 60);
$month = time() - (31 * 24 * 60 * 60);
$year = time() - (365 * 24 * 60 * 60);

?>
