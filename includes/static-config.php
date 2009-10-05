<?php

## ifDescr whitelist (used instead of ifName)
$config['ifdescr']['IOS'] = true;
$config['ifdescr']['IOS XE'] = true;
$config['ifname']['ASA'] = true;
$config['ifname']['CatOS'] = true;


## AFI / SAFI pairs for BGP (and other stuff, perhaps)
$config['afi']['ipv4']['unicast']    = "IPv4";
$config['afi']['ipv4']['multiicast'] = "IPv4 Multicast";
$config['afi']['ipv4']['vpn']        = "VPNv4";
$config['afi']['ipv6']['unicast']    = "IPv6";
$config['afi']['ipv6']['multicast']  = "IPv6 Multicast";

if(!$config['graph_colours']['greens']) {
  $config['graph_colours']['greens']  = array('B6D14B','91B13C','6D912D','48721E','24520F','003300');
}
if(!$config['graph_colours']['pinks']) {
  $config['graph_colours']['pinks']   = array('D0558F','B34773','943A57','792C38','5C1F1E','401F10');
}
if(!$config['graph_colours']['blues']) {
  $config['graph_colours']['blues']   = array('A0A0E5','8080BD','606096','40406F','202048','000033');
}
if(!$config['graph_colours']['purples']) {
  $config['graph_colours']['purples'] = array('CC7CCC','AF63AF','934A93','773177','5B185B','3F003F');
}
if(!$config['graph_colours']['default']) {
  $config['graph_colours']['default'] = $config['graph_colours']['blues'];
}

##############################
# No changes below this line #
##############################

$config['version'] = "0.6.0";

if($config['enable_nagios']) {

$nagios_link = mysql_connect($config['nagios_db_host'], $config['nagios_db_user'], $config['nagios_db_pass']);
if (!$nagios_link) {
        echo "<h2>Nagios MySQL Error</h2>";
        die;
}
$nagios_db = mysql_select_db($config['nagios_db_name'], $nagios_link);

}

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
