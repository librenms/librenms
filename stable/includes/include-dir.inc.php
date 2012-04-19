<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 *
 * @package    observium
 * @subpackage functions
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */


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
