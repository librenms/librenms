<?php

if($_SESSION['userlevel'] == '10') {

echo("<pre>");
print_r($config);
echo("</pre>");

} else {

echo "<div class='errorbox'>Insufficient permissions to view this page.</div>";

}


?>
