<?php

foreach (glob($config['install_dir'].'/'.$include_dir.'/*.inc.php') as $file) {
    include $file;
}

unset($include_dir);
