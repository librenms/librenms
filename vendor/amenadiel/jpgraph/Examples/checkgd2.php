<?php // content="text/plain; charset=utf-8"
$im =  imagecreatetruecolor ( 300, 200);
$black = imagecolorallocate ($im,  0, 0, 0);
$lightgray = imagecolorallocate ($im,  230, 230, 230);
$darkgreen = imagecolorallocate ($im,  80, 140, 80);
$white = imagecolorallocate ($im,  255, 255, 255);

imagefilledrectangle ($im,0,0,299,199 ,$lightgray);
imagerectangle ($im,0,0,299,199,$black);
imagefilledellipse ($im,150,100,210,110,$white);
imagefilledellipse ($im,150,100,200,100,$darkgreen);
header ("Content-type: image/png");
imagepng ($im);
?>
