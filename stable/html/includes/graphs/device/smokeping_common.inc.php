<?php

  if (isset($config['smokeping']['dir'])) {
    $smokeping_files      = array();
    if ($handle = opendir($config['smokeping']['dir'])) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if (eregi(".rrd", $file)) {
                   if (eregi("~", $file)) {
                      list($target,$slave) = explode("~", str_replace(".rrd", "", $file));
                      $target = str_replace("_", ".", $target);
                      $smokeping_files['in'][$target][$slave] = $file;
                      $smokeping_files['out'][$slave][$target] = $file;
                   } else {
                      $target = str_replace(".rrd", "", $file);
                      $target = str_replace("_", ".", $target);
                      $smokeping_files['in'][$target][$config['own_hostname']] = $file;
                      $smokeping_files['out'][$config['own_hostname']][$target] = $file;
                   }
                }
            }
        }
    }
 }

?>
