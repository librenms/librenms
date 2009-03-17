<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_bar.php");

$month=array(
"Jan","Feb","Mar","Apr","Maj","Jun","Jul","Aug","Sep","Okt","Nov","Dec");

// Create datapoints where every point
$steps=100;
for($i=0; $i<$steps; ++$i) {
	$datay[$i]=log(pow($i,$i/10)+1)*sin($i/15)+35;
	$databarx[]=sprintf("198%d %s",floor($i/12),$month[$i%12]);
	
	// Simulate an accumulated value for every 5:th data point
	if( $i % 6 == 0 ) {
		$databary[]=abs(25*sin($i)+5);
	}
	else {
		$databary[]=0;
	}
	
}


// New graph with a background image and drop shadow
$graph = new Graph(450,300,"auto");
$graph->SetBackgroundImage("tiger_bkg.png",BGIMG_FILLFRAME);
$graph->SetShadow();

// Use an integer X-scale
$graph->SetScale("textlin");

// Set title and subtitle
$graph->title->Set("Combined bar and line plot");
$graph->subtitle->Set("100 data points, X-Scale: 'text'");

// Use built in font
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Make the margin around the plot a little bit bigger
// then default
$graph->img->SetMargin(40,140,40,80);	

// Slightly adjust the legend from it's default position in the
// top right corner to middle right side
$graph->legend->Pos(0.05,0.5,"right","center");

// Display every 10:th datalabel
$graph->xaxis->SetTextTickInterval(6);
$graph->xaxis->SetTextLabelInterval(2);
$graph->xaxis->SetTickLabels($databarx);
$graph->xaxis->SetLabelAngle(90);

// Create a red line plot
$p1 = new LinePlot($datay);
$p1->SetColor("red");
$p1->SetLegend("Pressure");

// Create the bar plot
$b1 = new BarPlot($databary);
$b1->SetLegend("Temperature");
$b1->SetAbsWidth(6);
$b1->SetShadow();

// The order the plots are added determines who's ontop
$graph->Add($p1);
$graph->Add($b1);

// Finally output the  image
$graph->Stroke();

?>


