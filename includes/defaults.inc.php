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
 * @subpackage config
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

//
// Please don't edit this file -- make changes to the configuration array in config.php
//

error_reporting(E_ERROR);

// Default directories

$config['temp_dir']      = "/tmp";
$config['install_dir']   = "/opt/librenms";
$config['log_dir']       = $config['install_dir'] . "/logs";

// Location of executables

$config['fping']          = "/usr/bin/fping";

// Web Interface Settings

if (isset($_SERVER["SERVER_NAME"]) && isset($_SERVER["SERVER_PORT"]))
{
  if (strpos($_SERVER["SERVER_NAME"] , ":"))
  {
    # Literal IPv6
    $config['base_url']  = "http://[" . $_SERVER["SERVER_NAME"] ."]" . ($_SERVER["SERVER_PORT"] != 80 ? ":".$_SERVER["SERVER_PORT"] : '') ."/";
  }
  else
  {
    $config['base_url']  = "http://" . $_SERVER["SERVER_NAME"] . ($_SERVER["SERVER_PORT"] != 80 ? ":".$_SERVER["SERVER_PORT"] : '') ."/";
  }
}

$config['vertical_summary'] = 0; // Enable to use vertical summary on front page instead of horizontal

# Enable daily updates
$config['update'] = 1;

$config['authlog_purge']                                  = 30; # Number in days of how long to keep authlog entries for.
$config['perf_times_purge']                               = 30; # Number in days of how long to keep performace pooling stats  entries for.

# Date format for PHP date()s
$config['dateformat']['long']                             = "r"; # RFC2822 style
$config['dateformat']['compact']                          = "Y-m-d H:i:s";
$config['dateformat']['time']                             = "H:i:s";

$config['enable_clear_discovery']                        = 1;// Set this to 0 if you want to disable the web option to rediscover devices
$config['front_page_down_box_limit'] = 10;

$config['enable_footer']                                 = 1;// Set this to 0 if you want to disable the footer copyright in the web interface
$config['api_demo']                                      = 0;// Set this to 1 if you want to disable some untrusting features for the API

?>
