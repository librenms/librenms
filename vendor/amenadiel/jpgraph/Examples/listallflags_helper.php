<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_flags.php';

if( empty($_GET['size']) ) {
    $size = FLAGSIZE2;
}
else {
    $size = $_GET['size'];
}

if( empty($_GET['idx']) ) {
    $idx = 'ecua';
}
else {
    $idx = $_GET['idx'];
}


$flags = new FlagImages($size) ;
$img = $flags->GetImgByIdx($idx);
header ("Content-type: image/png");
ImagePng ($img);

?>
