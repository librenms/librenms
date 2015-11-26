<?php
session_start();
if(isset($_REQUEST['width']) AND isset($_REQUEST['height'])) {
    $_SESSION['screen_width'] = $_REQUEST['width'];
    $_SESSION['screen_height'] = $_REQUEST['height'];
}
echo $_SESSION['screen_width'];
echo $_SESSION['screen_height'];
