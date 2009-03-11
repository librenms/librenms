<?php 

include("../config.php");
include("../includes/functions.php");
include("includes/authenticate.inc");

if (isset($_GET["dir"])) {
  $dir = $_GET["dir"];
} else {
  $dir = "h";
};

if (isset($_GET["width"])) {
  $length = $_GET["width"];
} else {
  $length = 200;
};

if (isset($_GET["per"])) {
  $percent = $_GET["per"];
} else {
  $percent = 0;
};

//calculate length of percent full
$percentlength = round(($percent / 100) * $length);

//send headers
Header("Content-Type: image/png");

if ($dir == "v") {
  //create image
  $image = ImageCreate(6, $length);
} else {
  //dir == h
  //create image
  $image = ImageCreate($length, 6);
};

//Make colours
$grey = ImageColorAllocate($image, 200, 200, 200);
if($percent < '40') {
$colour = ImageColorAllocate($image, 0, 128, 0);
} elseif($percent < '70') {
$colour = ImageColorAllocate($image, 0, 0, 128);
} else {
$colour = ImageColorAllocate($image, 128, 0, 0);
}

//Fill image with grey
ImageFill($image, 0, 0, $grey);

if ($dir == "v") {
  //create colour percent bar
  ImageFilledRectangle($image, 0, $length - $percentlength, 6, $length , $colour);
} else {
  //dir == h
  //create colour percent bar
  ImageFilledRectangle($image, 0, 0, $percentlength, 6, $colour);
};

//send picture to browser
$border = @imagecolorallocate($percent, 250, 250, 250);
imagerectangle($image, 0, 0, $length, 6, $border);

ImagePNG($image);

//clean up image as to not to crash the server
@imagedestroy($image);

?> 
