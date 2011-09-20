<?php

include("includes/geshi/geshi.php");

if ($_SESSION['userlevel'] >= "7")
{

  if (!is_array($config['rancid_configs'])) { $config['rancid_configs'] = array($config['rancid_configs']); }

  foreach ($config['rancid_configs'] as $configs)
  {
    if ($configs[strlen($configs)-1] != '/') { $configs .= '/'; }
    if (is_file($configs . $device['hostname'])) { $file = $configs . $device['hostname']; }
  }

  echo('<div style="clear: both;">');

  print_optionbar_start('', '');

  echo("<span style='font-weight: bold;'>Config</span> &#187; ");

  if (!$_GET['optc']) {
    echo('<span class="pagemenu-selected">');
    echo("<a href='device/".$device['device_id']."/showconfig/'> Latest</a>");
    echo("</span>");
  } else {
    echo("<a href='device/".$device['device_id']."/showconfig/'> Latest</a>");
  }

  if (function_exists('svn_log')) {

    $sep     = " | ";
    $svnlogs = svn_log($file, SVN_REVISION_HEAD, NULL, 5);
    $revlist = array();

    foreach ($svnlogs as $svnlog) {

      echo($sep);
      $revlist[] = $svnlog["rev"];

      if ($_GET['optc'] == $svnlog["rev"]) { echo('<span class="pagemenu-selected">'); }

      echo("<a href='device/".$device['device_id']."/showconfig/" . $svnlog["rev"] .  "/'> r" . $svnlog["rev"] ." <small>". date("d M H:i", strtotime($svnlog["date"])) . "</small></a>");
      if ($_GET['optc'] == $svnlog["rev"]) { echo("</span>");  }

      $sep = " | ";
    }
  }

  print_optionbar_end();

  if (function_exists('svn_log') && in_array($_GET['optc'], $revlist)) {
    list($diff, $errors) = svn_diff($file, $_GET['optc']-1, $file, $_GET['optc']);
    if (!$diff) {
      $text = "No Difference";
    } else {
      $text = "";
      while (!feof($diff)) { $text .= fread($diff, 8192); }
      fclose($diff);
      fclose($errors);
    }

  } else {
    $fh = fopen($file, 'r') or die("Can't open file");
    $text = fread($fh, filesize($file));
    fclose($fh);
  }

  if ($config['rancid_ignorecomments'])
  {
    $lines = explode("\n",$text);
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