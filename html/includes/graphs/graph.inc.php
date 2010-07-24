<?php

if($_GET['debug']) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('allow_url_fopen', 0);
  ini_set('error_reporting', E_ALL);
}

  include("../includes/defaults.inc.php");
  include("../config.php");
  include("../includes/common.php");
  include("../includes/rewrites.php");
  include("includes/authenticate.inc.php");

  if(!$config['allow_unauth_graphs']) {
    if(!$_SESSION['authenticated']) { echo("not authenticated"); exit; }
  }
  
  if($_GET['device']) {
    $_GET['id'] = $_GET['device'];
    $device_id = $_GET['device'];
  } elseif($_GET['if']) {
    $_GET['id'] = $_GET['if'];
  } elseif($_GET['port']) {
    $_GET['id'] = $_GET['port'];
  } elseif($_GET['peer']) {
    $_GET['id'] = $_GET['peer'];
  }

  preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', mres($_GET['type']), $graphtype);

  $type = $graphtype['type'];
  $subtype = $graphtype['subtype'];

  if($debug) {print_r($graphtype);}

  $from     = mres($_GET['from']);
  $to       = mres($_GET['to']);
  $width    = mres($_GET['width']);
  $height   = mres($_GET['height']);
  $title    = mres($_GET['title']);
  $vertical = mres($_GET['vertical']);
  $legend   = mres($_GET['legend']);
  $id       = mres($_GET['id']);

  $graphfile = $config['temp_dir'] . "/"  . strgen() . ".png";

  $os = gethostosbyid($device_id);
  if($config['os'][$os]['group']) {$os_group = $config['os'][$os]['group'];}

#  if(is_file($config['install_dir'] . "/html/includes/graphs/".$type."_".$os.".inc.php")) {
#    /// Type + OS Specific
#    include($config['install_dir'] . "/html/includes/graphs/".$type."_".$os.".inc.php");
#  }elseif($os_group && is_file($config['install_dir'] . "/html/includes/graphs/".$type."_".$os_group.".inc.php")) {
#    /// Type + OS Group Specific
#    include($config['install_dir'] . "/html/includes/graphs/".$type."_".$os_group.".inc.php");
#  } elseif(is_file($config['install_dir'] . "/html/includes/graphs/$type.inc.php")) {
#    /// Type Specific
#    include($config['install_dir'] . "/html/includes/graphs/$type.inc.php");
#  }

if(is_file($config['install_dir'] . "/html/includes/graphs/$type/$subtype.inc.php")) {
  include($config['install_dir'] . "/html/includes/graphs/$type/$subtype.inc.php");
}


  if($rrd_options) {
    
    if($config['rrdcached']) { $rrd_switches = " --daemon ".$config['rrdcached'] . " "; }
    $rrd_cmd = $config['rrdtool'] . " graph $graphfile $rrd_options" . $rrd_switches;
    $woo = shell_exec($rrd_cmd);
    if($_GET['debug']) { echo("<pre>".$rrd_cmd."</pre>"); }    
#    $thing = popen($config['rrdtool'] . " -",'w');
#    fputs($thing, "graph $graphfile $rrd_options");
#    pclose($thing);
    if(is_file($graphfile)) {
      header('Content-type: image/png');
      $fd = fopen($graphfile,'r');fpassthru($fd);fclose($fd);
      unlink($graphfile);
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
  }



?>
