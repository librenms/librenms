<?php
include ("../jpgraph.php");
include ("../jpgraph_scatter.php");

$polex = 6;
$poley = 40;

function FldCallback($x,$y,$a) {
    GLOBAL $polex, $poley;
    $maxr = 3000;

    // Size and arrow size is constant
    $size="";
    $arrowsize="";

    // Since we have different scales we need the data points
    // to be of the same magnitude to give it a distance
    // interpretation.
    $x *= 10; 

    // Colors gets colder the further out we go from the center
    $r = ($x-$polex*10)*($x-$polex*10)+($y-$poley)*($y-$poley);
    $f = $r/$maxr;
    if( $f > 1 ) $f=1;
    $red = floor((1-$f)*255);
    $blue = floor($f*255);
    $color = array($red,0,$blue);
    //echo "x=$x, y=$y, blue=$blue, red=$red<br>";
    return array($color,$size,$arrowsize);
}

// Create data for a simulated pseudo-magnetic radient field
$datax = array();
$datay = array();
$angle = array();
for($x=1; $x < 10; ++$x ) {
    for($y=10; $y<100; $y += 10) {
	$a = -1;
	if( $x==$polex && $y==$poley ) continue;
	if( $x==$polex ) {
	    if( $y > $poley ) $a=90;
	    else $a = 270;
	}
	if( $y==$poley ) {
	    if( $x  > $polex ) $a=0;
	    else  $a=180;	    
	}
	if( $a == -1 ) {
	    $d1 = $y-$poley;
	    $d2 = ($polex-$x)*20;
	    if( $y < $poley ) $d2 *= -1;
	    $h = sqrt($d1*$d1+$d2*$d2);
	    $t = -$d2/$h;
	    $ac = acos($t);
	    if( $y < $poley ) $ac += M_PI;
	    $a = $ac * 180/M_PI;
	}
	$datax[] = $x;
	$datay[] = $y;
	$angle[] = $a;
    }
}

// Setup the graph
$graph = new Graph(300,200);
$graph->SetScale("intlin",0,100,0,10);
$graph->SetMarginColor('lightblue');


// ..and titles
$graph->title->Set("Field plot");

// Setup the field plot
$fp = new FieldPlot($datay,$datax,$angle);

// Setup formatting callback
$fp->SetCallback('FldCallback');

// First size argument is length (in pixels of arrow) 
// Second size argument is roughly size of arrow. Arrow size is specified as
// an integer in the range [0,9]
$fp->arrow->SetSize(20,2);
$fp->arrow->SetColor('navy');

$graph->Add($fp);

// .. and output
$graph->Stroke();

?>


