<?php

#print_r($_GET);

$file = $config['install_dir'] . "/html/pages/ports/" . safename($_GET['opta']) . ".inc.php";

if(is_file($file)) { include($file); } else { include("ports/default.inc.php"); }


?>
