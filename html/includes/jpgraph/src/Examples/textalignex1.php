<?php
// $Id: textalignex1.php,v 1.1 2002/10/19 17:42:53 aditus Exp $
require_once "../jpgraph.php";
require_once "../jpgraph_canvas.php";

if( empty($_GET['a']) ) {
    $angle=40;
}
else {
    $angle=$_GET['a'];
}

$caption = "Demonstration of different anchor points for texts as specified with\nTextAlign(). The red cross marks the coordinate that was given to\nstroke each instance of the string.\n(The green box is the bounding rectangle for the text.)";
$txt="TextAlign()";


// Initial width and height since we need a "dummy" canvas to
// calculate the height of the text strings
$w=480;$h=50;
$xm=90;$ym=80;

$g = new CanvasGraph($w,$h);

$aImg = $g->img;
$aImg->SetFont(FF_ARIAL,FS_NORMAL,16);
$tw=$aImg->GetBBoxWidth($txt,$angle);
$th=$aImg->GetBBoxHeight($txt,$angle);

$aImg->SetFont(FF_ARIAL,FS_NORMAL,11);
$ch=$aImg->GetBBoxHeight($caption);

// Calculate needed height for the image
$h = 3*$th+2*$ym + $ch;
$g = new CanvasGraph($w,$h);
$aImg = $g->img;

$prof = array('left','top',
	      'center','top',
	      'right','top',
	      'left','center',
	      'center','center',
	      'right','center',
	      'left','bottom',
	      'center','bottom',
	      'right','bottom');
$n = count($prof)/2;

for( $i=0,$r=0,$c=0; $i < $n; ++$i ) {
    $x = $c*($tw+$xm)+$xm/2;
    $y = $r*($th+$ym)+$ym/2-10;
    $aImg->SetColor('blue');
    $aImg->SetTextAlign($prof[$i*2],$prof[$i*2+1]);			
    $aImg->SetFont(FF_ARIAL,FS_NORMAL,16);
    $aImg->StrokeText($x,$y,$txt,$angle,"left",true);

    $aImg->SetColor('black');
    $aImg->SetFont(FF_FONT1,FS_BOLD);
    $aImg->SetTextAlign('center','top');			
    $align = sprintf('("%s","%s")',$prof[$i*2],$prof[$i*2+1]);
    $aImg->StrokeText($c*($tw/2+$xm)+$xm/2+$tw/2,$r*($th/2+$ym)+$th+$ym/2-4,$align);
    $c++;
    if( $c==3 ) {
	$c=0;$r++;
    }
}

$aImg->SetTextAlign('center','bottom');			
$aImg->SetFont(FF_ARIAL,FS_ITALIC,11);
$aImg->StrokeText($w/2,$h-10,$caption,0,'left');

$aImg->SetColor('navy');
$aImg->Rectangle(0,0,$w-1,$h-1);

$g->Stroke();

?>

