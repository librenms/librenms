<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_bar.php");

// Some data

$steps=100;
for($i=0; $i<$steps; ++$i) {
	$datay[$i]=log(pow($i,$i/10)+1)*sin($i/15)+35;
	$datax[]=$i;
	if( $i % 10 == 0 ) {
		$databarx[]=$i;
		$databary[]=$datay[$i]/2;
	}
}

// New graph with a background image and drop shadow
$graph = new Graph(450,300,"auto");
$graph->SetBackgroundImage("tiger_bkg.png",BGIMG_FILLFRAME);
$graph->SetShadow();

// Use an integer X-scale
$graph->SetScale("intlin");

// Set title and subtitle
$graph->title->Set("Combined bar and line plot");
$graph->subtitle->Set("(\"left\" aligned bars)");

// Use built in font
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Make the margin around the plot a little bit bigger
// then default
$graph->img->SetMargin(40,120,40,40);	

// Slightly adjust the legend from it's default position in the
// top right corner to middle right side
$graph->legend->Pos(0.05,0.5,"right","center");

// Create a red line plot
$p1 = new LinePlot($datay,$datax);
$p1->SetColor("red");
$p1->SetLegend("Status one");
$graph->Add($p1);

// Create the bar plot
$b1 = new BarPlot($databary,$databarx);
$b1->SetLegend("Status two");
$b1->SetAlign("left");
$b1->SetShadow();
$graph->Add($b1);

// Finally output the  image
$graph->Stroke();

?>


