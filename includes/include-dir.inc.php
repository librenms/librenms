<?php

### This is an include so that we don't lose variable scope.

  if ($include_dir_regexp == "" || !isset($include_dir_regexp))
  {
    $include_dir_regexp = "/\.inc\.php$/";
  }

  if ($handle = opendir($config['install_dir'] . '/' . $include_dir))
  {
    while (false !== ($file = readdir($handle)))
    {
      if (filetype($config['install_dir'] . '/' . $include_dir . '/' . $file) == 'file' && preg_match($include_dir_regexp, $file))
      {
        if ($debug) { echo("Including: " . $config['install_dir'] . '/' . $include_dir . '/' . $file . "\n"); }
        include($config['install_dir'] . '/' . $include_dir . '/' . $file);
      }
    }
    closedir($handle);
  }

  unset($include_dir_regexp, $include_dir);

?>
