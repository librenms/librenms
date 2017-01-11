<?php // content="text/plain; charset=utf-8"

if( empty($_GET['id']) ) {
    echo 'Incorrect argument(s) to script <b>'.basename(__FILE__).'</b>.'; 
}
else {
    echo 'Some details on bar with id='.$_GET['id'];
}

?>
