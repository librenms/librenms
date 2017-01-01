<?php // content="text/plain; charset=utf-8"
// $Id: canvaspiralex1.php,v 1.1 2002/10/26 11:35:42 aditus Exp $
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');


if( empty($_GET['r']) ) 
    $r = 0.44;
else
    $r = $_GET['r'];

if( empty($_GET['w']) ) 
    $w=150;
else
    $w = $_GET['w'];

if( empty($_GET['h']) ) 
    $h=240;
else
    $h = $_GET['h'];

if( $w < 60 ) $w=60;
if( $h < 60 ) $h=60;


function SeaShell($img,$x,$y,$w,$h,$r,$n=12,$color1='navy',$color2='red') {

    $x += $w;
    $w = (1-$r)/$r*$w;

    $sa = 0;
    $ea = 90;

    $s1 = 1;
    $s2 = -1;
    $x_old=$x; $y_old=$y;
    for($i=1; $i < $n; ++$i) {
	$sa += 90;
	$ea += 90;
	if( $i % 2 == 1 ) {
	    $y = $y + $s1*$h*$r;
	    $h = (1-$r)*$h;
	    $w = $w / (1-$r) * $r ;
	    $s1 *= -1;
	    $img->SetColor($color1);
	    $img->Line($x,$y,$x+$s1*$w,$y);
	}
	else {
	    $x = $x + $s2*$w*$r;
	    $w = (1-$r)*$w;
	    $h = $h / (1-$r) * $r;
	    $s2 *= -1;
	    $img->SetColor($color1);
	    $img->Line($x,$y,$x,$y-$s2*$h);
	}
	$img->SetColor($color2);
	$img->FilledRectangle($x-1,$y-1,$x+1,$y+1);
	$img->Arc($x,$y,2*$w+1,2*$h+1,$sa,$ea);
	$img->Arc($x,$y,2*$w,2*$h,$sa,$ea);
	$img->Arc($x,$y,2*$w-1,2*$h-1,$sa,$ea);
	$img->Line($x_old,$y_old,$x,$y);
	$x_old=$x; $y_old=$y;
    }
}

$g = new CanvasGraph($w,$h);
//$gr = 1.61803398874989484820;

$p = SeaShell($g->img,0,20,$w-1,$h-21,$r,19);
$g->img->SetColor('black');
$g->img->Rectangle(0,20,$w-1,$h-1);
$g->img->SetFont(FF_FONT2,FS_BOLD);
$g->img->SetTextAlign('center','top');
$g->img->StrokeText($w/2,0,"Canvas Spiral");

$g->Stroke();
?>

