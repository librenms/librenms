<?php 
    // returns a PNG graph from the $_GET['per'] variable 
    $width = '201';
    $height = '7';
    $per = imagecreate($width,$height); 
    $background = imagecolorallocate($per, 0xFF, 0xFF, 0xFF); 
    $foreground = imagecolorallocate($per, 0x00, 0x8A, 0x01); 
    $border = imagecolorallocate($per, 0x99, 0x99, 0x99); 
    if ($_GET['per'] > 0) {
        $grad = imagecreatefrompng("images/grad.png"); 
        $per2 = imagecopy($per, $grad, 1, 1, 0, 0, ($_GET['per'] * $width / 100), 5); 
        imagerectangle($per, 0, 0, $width-1, $height-1 , $border); 
    } 
    header("Content-type: image/png"); 
    imagepng($per, '', 100); 
?> 
