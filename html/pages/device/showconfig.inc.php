<?php

include("includes/geshi/geshi.php");

if($_SESSION['userlevel'] >= "7") {

  if(!is_array($config['rancid_configs'])) { $config['rancid_configs'] = array($config['rancid_configs']); }
  
  foreach($config['rancid_configs'] as $configs) {
    if ($configs[strlen($configs)-1] != '/') { $configs .= '/'; }
    if(is_file($configs . $device['hostname'])) { $file = $configs . $device['hostname']; }
  }

  $fh = fopen($file, 'r') or die("Can't open file");
  $text = fread($fh, filesize($file));
  fclose($fh);

  if ($config['rancid_ignorecomments'])
  {
    $lines = split("\n",$text);
    for ($i = 0;$i < count($lines);$i++)
    {
      if ($lines[$i][0] == "#") { unset($lines[$i]); }
    }
    $text = join("\n",$lines);
  }
  $language = "ios";
  $geshi = new GeSHi($text, $language);
  $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
  $geshi->set_overall_style('color: black;');
  #$geshi->set_line_style('color: #999999'); 
  echo($geshi->parse_code());
}

?>
