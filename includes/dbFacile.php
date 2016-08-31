<?php

if (file_exists($config['install_dir'].'/includes/dbFacile.'.$config['db']['extension'].'.php')) {
    require_once $config['install_dir'].'/includes/dbFacile.'.$config['db']['extension'].'.php';
} else {
    echo $config['db']['extension'] . " extension not found\n";
    exit;
}
