<?php // content="text/plain; charset=utf-8"
$im = @imagecreate (200, 100) or die ( "cannot create a new gd image.");
$background_color = imagecolorallocate ($im, 240, 240, 240);
$border_color = imagecolorallocate ($im, 50, 50, 50);
$text_color = imagecolorallocate ($im, 233, 14, 91);

imagerectangle($im,0,0,199,99,$border_color);
imagestring ($im, 5, 10, 40, "a simple text string", $text_color );
header ("content-type: image/png");
imagepng ($im);
?>
