<?php

session_start();

if (isset($_REQUEST['width'], $_REQUEST['height'])) {
    $_SESSION['screen_width'] = $_REQUEST['width'];
    $_SESSION['screen_height'] = $_REQUEST['height'];
}

session_write_close();

header('Content-type: text/plain');
echo $_SESSION['screen_width'] . 'x' . $_SESSION['screen_height'];
