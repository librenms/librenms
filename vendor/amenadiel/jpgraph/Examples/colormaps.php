<?php // content="text/plain; charset=utf-8"
require_once ('../jpgraph.php');
require_once ('../jpgraph_canvas.php');
require_once ('../jpgraph_colormap.inc.php');

class ColorMapDriver {
	const WIDTH = 600; // Image width
	const LMARG = 90;  // Left margin
	const RMARG = 25;  // Right margin
	const MAPMARG = 35;  // Map margin between each map
	const MODHEIGHT = 30; // Module height (=Map height)
	const YSTART = 60; // Start coordinate for map list

    public function Draw($aTitle, $aStart, $aEnd, $n=64, $aReverse=false, $addColorNames = false ) {

	    // Setup to draw colormap with names platoe colors
	    $lmarg = ColorMapDriver::LMARG; // left margin
	    $rmarg = ColorMapDriver::RMARG; // right margin
	    $width = ColorMapDriver::WIDTH; // Overall image width

	    // Module height
	    $mh = ColorMapDriver::MODHEIGHT;

	    // Step between each map
	    $ymarg = $mh + ColorMapDriver::MAPMARG;

	    if( $addColorNames ) {
	    	$ymarg += 50;
	    }

        // Start position
        $xs=$lmarg; $ys=ColorMapDriver::YSTART;

    	// Setup a basic canvas graph
        $height = ($aEnd-$aStart+1)*$ymarg+50;
        $graph = new CanvasGraph($width,$height);
        $graph->img->SetColor('darkgray');
        $graph->img->Rectangle(0,0,$width-1,$height-1);

	    $t = new Text($aTitle, $width/2,5);
	    $t->SetAlign('center','top');
	    $t->SetFont(FF_ARIAL,FS_BOLD,14);
	    $t->Stroke($graph->img);

	    // Instantiate a colormap
		$cm = new ColorMap();
		$cm->InitRGB($graph->img->rgb);

        for( $mapidx=$aStart; $mapidx <= $aEnd; ++$mapidx, $ys += $ymarg ) {

	        $cm->SetMap($mapidx,$aReverse);
	        $n = $cm->SetNumColors($n);
	        list( $mapidx, $maparray ) = $cm->GetCurrMap();
	        $ncols = count($maparray);
	        $colbuckets = $cm->GetBuckets();

	        // The module width will depend on the actual number of colors
	    	$mw = round(($width-$lmarg-$rmarg)/$n);

	        // Draw color map title (name)
	        $t->Set('Basic colors: '.$ncols.',   Total colors: '.$n);
	        $t->SetAlign('center','bottom');
	        $t->SetAngle(0);
	        $t->SetFont(FF_TIMES,FS_NORMAL,14);
	        $t->Stroke($graph->img,$width/2,$ys-3);

	        // Add the name/number of the map to the left
	        $t->SetAlign('right','center');
	        $t->Set('Map: '.$mapidx);
	        $t->SetFont(FF_ARIAL,FS_NORMAL,14);
	        $t->Stroke($graph->img,$xs-20,round($ys+$mh/2));

	        // Setup text properties for the color names
	        if( $addColorNames ) {
	        	$t->SetAngle(30);
	        	$t->SetFont(FF_ARIAL,FS_NORMAL,12);
	        	$t->SetAlign('right','top');
	        }

	        // Loop through all colors in the map
	        $x = $xs; $y = $ys; $k=0;
	        for($i=0; $i < $n; ++$i){
	            $graph->img->SetColor($colbuckets[$i]);
	            $graph->img->FilledRectangle($x,$y,$x+$mw,$y+$mh);

	            // Mark all basic colors in the map with a bar and name
	            if( $i % (($n-$ncols)/($ncols-1)+1) == 0 ) {
	            	$graph->img->SetColor('black');
	            	$graph->img->FilledRectangle($x,$y+$mh+4,$x+$mw-1,$y+$mh+6);
	            	if( $addColorNames ) {
	            		$t->Set($maparray[$k++]);
	            		$t->Stroke($graph->img,$x+$mw/2,$y+$mh+10);
	            	}
	            }
	            $x += $mw;
	        }

	        // Draw a border around the map
	        $graph->img->SetColor('black');
	        $graph->img->Rectangle($xs,$ys,$xs+$mw*$n,$ys+$mh);

	    }

        // Send back to client
        $graph->Stroke();
    }

}

$driver = new ColorMapDriver();

$title = "Standard maps";
$reverse = false;
$n = 64; $s=0; $e=9;
$showNames = false;


/*
$title = "Center maps";
$reverse = false;
$n = 64; $s=10; $e=14;
$showNames = false;
*/

/*
$title = "Continues maps";
$reverse = false;
$n = 64; $s=15; $e=21;
$showNames = false;
*/
$driver->Draw($title,$s,$e,$n,$reverse,$showNames);

?>
