<?php


if($_GET['debug']) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('allow_url_fopen', 0);
  ini_set('error_reporting', E_ALL);
}

  include("../config.php");
  include("../includes/common.php");
  include("../includes/graphing.php");
  include("includes/authenticate.inc");

#  if(!$_SESSION['authenticated']) { echo("not authenticated"); exit; }
  
  if($_GET['device']) {
    $device_id = $_GET['device'];
  } elseif($_GET['if']) {
    $device_id = getifhost($_GET['if']);
    $ifIndex = getifindexbyid($_GET['if']);
  } elseif($_GET['port']) {
    $device_id = getifhost($_GET['port']);
    $ifIndex = getifindexbyid($_GET['port']);
  } elseif($_GET['peer']) {
    $device_id = getpeerhost($_GET['peer']);
  }

  if($device_id) { $hostname = gethostbyid($device_id); }

  $from     = mres($_GET['from']);
  $to       = mres($_GET['to']);
  $width    = mres($_GET['width']);
  $height   = mres($_GET['height']);
  $title    = mres($_GET['title']);
  $vertical = mres($_GET['vertical']);
  $type     = mres($_GET['type']);

  $graphfile = $config['temp_dir'] . "/"  . strgen() . ".png";

  $os = gethostosbyid($device_id);
  $os_lower = strtolower($os);
  if($os_groups[$os_lower]) {$os_group = $os_groups[$os_lower];}

  if(is_file($config['install_dir'] . "/html/includes/graphs/".$type."_".$os_lower.".inc.php")) {
    /// Type + OS Specific
    include($config['install_dir'] . "/html/includes/graphs/".$type."_".$os_lower.".inc.php");
  }elseif($os_group && is_file($config['install_dir'] . "/html/includes/graphs/".$type."_".$os_group.".inc.php")) {
    /// Type + OS Group Specific
    include($config['install_dir'] . "/html/includes/graphs/".$type."_".$os_group.".inc.php");
  } elseif(is_file($config['install_dir'] . "/html/includes/graphs/$type.inc.php")) {
    /// Type Specific
    include($config['install_dir'] . "/html/includes/graphs/$type.inc.php");
  }

  if($rrd_options) {
    if($_GET['debug']) { echo("<pre>".$config['rrdtool'] . " graph $graphfile $rrd_options"); }
    $thing = shell_exec($config['rrdtool'] . " graph $graphfile $rrd_options");
    if(is_file($graphfile)) {
      header('Content-type: image/png');
      echo(`cat $graphfile`);
    } else {
      header('Content-type: image/png');
      $string = "Graph Generation Error";
      $im     = imagecreate($width, $height);
      $orange = imagecolorallocate($im, 255, 255, 255);
      $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
      imagestring($im, 3, $px, $height / 2 - 8, $string, imagecolorallocate($im, 128, 0, 0));
      imagepng($im);
      imagedestroy($im);
    }
  } else {

  switch ($type) {
  case 'cisco_entity_sensor':
    $graph = graph_entity_sensor ($_GET['a'], $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'fortigate_sessions':
    $graph = graph_fortigate_sessions ($hostname . "/fortigate-sessions.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'fortigate_cpu':
    $graph = graph_fortigate_cpu ($hostname . "/fortigate-cpu.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'fortigate_memory':
    $graph = graph_fortigate_memory ($hostname . "/fortigate-memory.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'netscreen_sessions':
    $graph = graph_netscreen_sessions ($hostname . "/netscreen-sessions.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'netscreen_cpu':
    $graph = graph_netscreen_cpu ($hostname . "/netscreen-cpu.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'netscreen_memory':
    $graph = graph_netscreen_memory ($hostname . "/netscreen-memory.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'multi_bits_duo':
    $groups = array($_GET['interfaces'], $_GET['interfaces_b']);
    $graph = graph_multi_bits_duo ($groups, $graphfile, $from, $to, $width, $height, $title, $vertical, $inverse, $legend);
    break;
  case 'multi_bits_trio':
    $groups = array($_GET['interfaces'], $_GET['interfaces_b'], $_GET['interfaces_c']);
    $graph = graph_multi_bits_trio ($groups, $graphfile, $from, $to, $width, $height, $title, $vertical, $inverse, $legend);
    break;
  case 'adsl_rate':
    $graph = graph_adsl_rate ($hostname. "/adsl-4.rrd", $graphfile, $from, $to, $width, $height);
    break;
  case 'adsl_snr':
    $graph = graph_adsl_snr ($hostname. "/adsl-4.rrd", $graphfile, $from, $to, $width, $height);
    break;
  case 'adsl_atn':
    $graph = graph_adsl_atn ($hostname. "/adsl-4.rrd", $graphfile, $from, $to, $width, $height);
    break;
  case 'global_bits':
    $graph = graph_global_bits ("global_bits.png", $from, $to, $width, $height);
    break;
  case 'mac_acc_int':
    $graph = graph_mac_acc_interface ($_GET['if'], $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'mac_acc_pkts':
    $graph = graph_mac_pkts ($_GET['id'], $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'mac_acc':
  case 'mac_acc_bits':
    $graph = graph_mac_acc ($_GET['id'], $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'unixfs_dev':
    $graph = unixfsgraph_dev ($device_id, $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'unixfs':
    $graph = unixfsgraph ($_GET['id'], $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'bgp_updates':
    $bgpPeerIdentifier = mysql_result(mysql_query("SELECT bgpPeerIdentifier FROM bgpPeers WHERE bgpPeer_id = '".$_GET['peer']."'"),0);
    $graph = bgpupdatesgraph ($hostname . "/bgp-" . $bgpPeerIdentifier . ".rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'cbgp_prefixes':
    $bgpPeerIdentifier = mysql_result(mysql_query("SELECT bgpPeerIdentifier FROM bgpPeers WHERE bgpPeer_id = '".$_GET['peer']."'"),0);
    $graph = graph_cbgp_prefixes ($hostname . "/cbgp-" . $bgpPeerIdentifier . ".".$_GET['afi'].".".$_GET['safi'].".rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'calls':
    $graph = callsgraphSNOM ($hostname . "/data.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'dev_cpmCPU':
      $graph = graph_device_cpmCPU ($device_id, $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'cpmCPU':
      $graph = graph_cpmCPU ($_GET['id'], $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'temp':
      $graph = temp_graph ($_GET['id'], $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'dev_temp':
      $graph = temp_graph_dev ($device_id, $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
    case 'cempMemPool':
      $graph = graph_cempMemPool ($_GET['id'], $graphfile, $from, $to, $width, $height, $title, $vertical);
    break;
  case 'mem':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD" || $os == "NetBSD" ) {
      $graph = memgraphUnix ($hostname . "/mem.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    } elseif($os == "IOS" || $os == "IOS XE") {
      $graph = graph_device_cempMemPool ($device_id, $graphfile, $from, $to, $width, $height, $title, $vertical);
    } elseif($os == "CatOS") {
      $graph = memgraph ($hostname . "/mem.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    } elseif($os == "Windows") {
    } elseif($os == "ProCurve") {
      $graph = memgraphHP ($hostname . "/mem.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  case 'load':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD" || $os == "NetBSD" ) {
      $graph = loadgraphUnix ($hostname . "/load.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    } elseif($os == "IOS" || $os == "IOS XE") {
      $graph = loadgraph ($hostname . "/load.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    } elseif($os == "Windows") {
      $graph = loadgraphwin ($hostname . "/load.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  case 'users':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD" || $os == "NetBSD" ) {
      $graph = usersgraphUnix ($hostname . "/sys.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    } elseif($os == "Windows") {
      $graph = usersgraphwin ($hostname . "/sys.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  case 'procs':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD" || $os == "NetBSD" ) {
      $graph = procsgraphUnix ($hostname . "/sys.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    } elseif($os == "Windows") {
      $graph = procsgraphwin ($hostname . "/sys.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  case 'unixfs':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD" || $os == "NetBSD" ) {
      $graph = unixfsgraph ($device_id, $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  case 'postfix':
  case 'mail':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD") {
      $graph = mailsgraphUnix ($hostname . "/mailstats.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  case 'mailerrors':
  case 'postfixerrors':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD") {
      $graph = mailerrorgraphUnix ($hostname, $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  case 'courier':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD") {
      $graph = couriergraphUnix ($hostname . "/courierstats.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  case 'apachehits':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD") {
      $graph = apachehitsgraphUnix ($hostname . "/apache.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  case 'apachebits':
    if($os == "Linux" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD") {
      $graph = apachebitsgraphUnix ($hostname . "/apache.rrd", $graphfile, $from, $to, $width, $height, $title, $vertical);
    }
    break;
  default:
    break;
  } // End SWITCH

  if($graph) {
    header('Content-type: image/png');
    echo(`cat $graphfile`);
  } else {  
    header('Content-type: image/png');
    $string = "Graph Generation Error";
    $im     = imagecreate($width, $height);
    $orange = imagecolorallocate($im, 255, 255, 255);
    $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
    imagestring($im, 3, $px, $height / 2 - 8, $string, imagecolorallocate($im, 128, 0, 0));
    imagepng($im);
    imagedestroy($im);
  }

  $delete = `rm $graphfile`; 

  } // End IF


?>
