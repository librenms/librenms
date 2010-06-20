<?php

if ($handle = opendir($config['install_dir'] . "/includes/polling/applications/")) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && strstr($file, ".inc.php")) {
            $file = str_replace(".inc.php", "", $file);
            $servicesform .= "<option value='$file'>$file</option>";
        }
    }
    closedir($handle);
}

echo("$servicesform");



?>
