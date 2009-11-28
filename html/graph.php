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
  include("../includes/rewrites.php");
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
  }

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

#  } // End IF


?>
