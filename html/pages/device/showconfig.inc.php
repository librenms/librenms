<?php

include("includes/geshi/geshi.php");

if($_SESSION[userlevel] >= "5" && is_file($config['rancid_configs'] . $device['hostname'])) {
  $file = $config['rancid_configs'] . $device['hostname'];
 # $file = "/testconfig.txt";
  $fh = fopen($file, 'r') or die("Can't open file");
  $text = fread($fh, filesize($file));
  $language = "ios";
  $geshi = new GeSHi($text, $language);
  $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
  $geshi->set_overall_style('color: black;');
#  $geshi->set_line_style('color: #999999'); 

# echo("<div style='width:1175px;height:540px;overflow-x:hidden;overflow-y:auto;'>");
 echo $geshi->parse_code();
# echo("</div>");
  fclose($fh);
} else {
  print_error("Error : Insufficient access.");
}


?>
