<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

DEFINE('WORLDMAP','worldmap1.jpg');

function markCallback($y,$x) {
    // Return array width
    // width,color,fill color, marker filename, imgscale
    // any value can be false, in that case the default value will
    // be used.
    // We only make one pushpin another color
    if( $x == 54 ) 
	return array(false,false,false,'red',0.8);
    else
	return array(false,false,false,'green',0.8);
}

// Data arrays
$datax = array(10,20,30,40,54,60,70,80);
$datay = array(12,23,65,18,84,28,86,44);

// Setup the graph
$graph = new Graph(400,270);

// We add a small 1pixel left,right,bottom margin so the plot area
// doesn't cover the frame around the graph.
$graph->img->SetMargin(1,1,1,1);
$graph->SetScale('linlin',0,100,0,100);

// We don't want any axis to be shown
$graph->xaxis->Hide();
$graph->yaxis->Hide();

// Use a worldmap as the background and let it fill the plot area
$graph->SetBackgroundImage(WORLDMAP,BGIMG_FILLPLOT);

// Setup a nice title with a striped bevel background
$graph->title->Set("Pushpin graph");
$graph->title->SetFont(FF_ARIAL,FS_BOLD,16);
$graph->title->SetColor('white');
$graph->SetTitleBackground('darkgreen',TITLEBKG_STYLE1,TITLEBKG_FRAME_BEVEL);
$graph->SetTitleBackgroundFillStyle(TITLEBKG_FILLSTYLE_HSTRIPED,'blue','darkgreen');

// Finally create the lineplot
$lp = new LinePlot($datay,$datax);
$lp->SetColor('lightgray');

// We want the markers to be an image
$lp->mark->SetType(MARK_IMG_PUSHPIN,'blue',0.6);

// Install the Y-X callback for the markers
$lp->mark->SetCallbackYX('markCallback');

// ...  and add it to the graph
$graph->Add($lp);    

// .. and output to browser
$graph->Stroke();

?>


