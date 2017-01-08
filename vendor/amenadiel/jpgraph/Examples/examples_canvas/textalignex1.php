<?php // content="text/plain; charset=utf-8"
require_once "jpgraph/jpgraph.php";
require_once "jpgraph/jpgraph_canvas.php";

// We accept a URI argument to adjust the angle at what we display the text
if( empty($_GET['a']) ) {
    $angle=40;
}
else {
    $angle=$_GET['a'];
}

// Caption below the image
$caption = "Demonstration of different anchor points for texts as specified with\n".
    "TextAlign(). The red cross marks the coordinate that was given to\n".
    "stroke each instance of the string.\n(The green box is the bounding rectangle for the text.)";

$txt="TextAlign()";


// Initial width and height since we need a "dummy" canvas to
// calculate the height of the text strings
$w=480;$h=50;
$xm=90;$ym=80;

$g = new CanvasGraph($w,$h);

// Make the image easier to access
$img = $g->img;

// Get the bounding box for text
$img->SetFont(FF_ARIAL,FS_NORMAL,16);
$tw=$img->GetBBoxWidth($txt,$angle);
$th=$img->GetBBoxHeight($txt,$angle);

$img->SetFont(FF_ARIAL,FS_NORMAL,11);
$ch=$img->GetBBoxHeight($caption);

// Calculate needed height for the image
$h = 3*$th+2*$ym + $ch;
$g = new CanvasGraph($w,$h);
$img = $g->img;

// Alignment for anchor points to use
$anchors = array('left','top',
              'center','top',
              'right','top',
              'left','center',
              'center','center',
              'right','center',
              'left','bottom',
              'center','bottom',
              'right','bottom');

$n = count($anchors)/2;

for( $i=0,$r=0,$c=0; $i < $n; ++$i ) {

    $x = $c*($tw+$xm)+$xm/2;
    $y = $r*($th+$ym)+$ym/2-10;

    $img->SetColor('blue');
    $img->SetTextAlign($anchors[$i*2],$anchors[$i*2+1]);
    $img->SetFont(FF_ARIAL,FS_NORMAL,16);
    $img->StrokeText($x,$y,$txt,$angle,"left",true);

    $img->SetColor('black');
    $img->SetFont(FF_FONT1,FS_BOLD);
    $img->SetTextAlign('center','top');
    $align = sprintf('("%s","%s")',$anchors[$i*2],$anchors[$i*2+1]);
    $img->StrokeText($c*($tw/2+$xm)+$xm/2+$tw/2,$r*($th/2+$ym)+$th+$ym/2-4,$align);

    $c++;
    if( $c==3 ) {
        $c=0;$r++;
    }
}

// Draw the caption text
$img->SetTextAlign('center','bottom');
$img->SetFont(FF_ARIAL,FS_ITALIC,11);
$img->StrokeText($w/2,$h-10,$caption,0,'left');

$img->SetColor('navy');
$img->Rectangle(0,0,$w-1,$h-1);

// .. and send back to browser
$g->Stroke();

?>

