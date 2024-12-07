<?php

use LibreNMS\Config;

foreach (glob(Config::get('install_dir') . '/' . $include_dir . '/*.inc.php') as $file) {
    include $file;
}

unset($include_dir);
