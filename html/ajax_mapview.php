<?php
session_start();
if (isset($_REQUEST['mapView'])) {
    $_SESSION['mapView'] = $_REQUEST['mapView'];
}
header('Content-type: text/plain');
echo $_SESSION['mapView'];
