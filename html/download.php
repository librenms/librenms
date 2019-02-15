<?php

header("Content-Disposition: attachment; filename=\"backup.conf\"");
header("Content-Type: application/force-download");
header("Content-Length: " . strlen($_POST['config']));
header("Connection: close");
echo $_POST['config'];
exit();
?>
