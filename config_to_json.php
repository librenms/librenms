<?php
/*

    Observium configuration to JSON converter
    Written by Job Snijders <job.snijders@atrato.com)

*/

$config_file = 'config.php';

// move to observium install dir
chdir(dirname($argv[0]));

function iscli() {
 
     if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
          return true;
     } else {
          return false;
     }
}

// check if we are running throw the CLI, otherwise abort

if ( iscli() ) {

    require_once($config_file);
    print(json_encode($config));
}

?>
