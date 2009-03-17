<?php

include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_bar.php");

// Some data

$steps=100;for($i=0; $i<$steps; ++$i) {	
    $datay[$i]=log(pow($i,$i/10)+1)*sin($i/15)+35;	
    $datax[]=$i;	
    if( $i % 10 == 0 ) {		
	$databarx[]=$i;		
	$databary[]=$datay[$i]/2;	
    }
}

// New graph with a background image and drop shadow
$graph = new Graph(450,300,"auto");
$graph->img->SetMargin(40,180,40,40);	
$graph->SetBackgroundImage("tiger_bkg.png",BGIMG_FILLFRAME);

//$graph->img->SetAntiAliasing();

$graph->SetScale("intlin");
$graph->SetShadow();
$graph->title->Set("Combined bar and line plot");
$graph->subtitle->Set("(\"center\" aligned bars)");

// Use built in font
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Slightly adjust the legend from it's default position in the
// top right corner. 
$graph->legend->Pos(0.05,0.5,"right","center");

// Create the first line

$p1 = new LinePlot($datay,$datax);
$p1->SetWeight(1);
$p1->SetColor("red");
$p1->SetLegend("Triumph Tiger -98");
$graph->Add($p1);

$b1 = new BarPlot($databary,$databarx);
$b1->SetAbsWidth(10);
$b1->SetAlign("center");
$b1->SetShadow();
$graph->Add($b1);

$graph->Stroke();
?>
