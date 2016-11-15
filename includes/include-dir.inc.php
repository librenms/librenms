<?php

foreach (glob($config['install_dir'].'/'.$include_dir.'/*.inc.php') as $file) {
    d_echo('Including: ' . $file . PHP_EOL);
    include $file;
}

unset($include_dir);
