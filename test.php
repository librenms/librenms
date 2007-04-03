#!/usr/bin/php

<?php

include("config.php");

$srvdir = $installdir . "/includes/services/";

if ($handle = opendir($srvdir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            echo "$file\n";
        }
    }
    closedir($handle);
}

?>
