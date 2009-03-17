<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");
include ("../jpgraph_line.php");

// Some "random" data
$ydata  = array(10,120,80,190,260,170,60,40,20,230);
$ydata2 = array(10,70,40,120,200,60,80,40,20,5);

// Get a list of month using the current locale
$months = $gDateLocale->GetShortMonth();

// Create the graph. 
$graph = new Graph(300,200);	
$graph->SetScale("textlin");
$graph->SetMarginColor('white');

// Adjust the margin slightly so that we use the 
// entire area (since we don't use a frame)
$graph->SetMargin(30,1,20,5);

// Box around plotarea
$graph->SetBox(); 

// No frame around the image
$graph->SetFrame(false);

// Setup the tab title
$graph->tabtitle->Set('Year 2003');
$graph->tabtitle->SetFont(FF_ARIAL,FS_BOLD,10);

// Setup the X and Y grid
$graph->ygrid->SetFill(true,'#DDDDDD@0.5','#BBBBBB@0.5');
$graph->ygrid->SetLineStyle('dashed');
$graph->ygrid->SetColor('gray');
$graph->xgrid->Show();
$graph->xgrid->SetLineStyle('dashed');
$graph->xgrid->SetColor('gray');

// Setup month as labels on the X-axis
$graph->xaxis->SetTickLabels($months);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
$graph->xaxis->SetLabelAngle(45);

// Create a bar pot
$bplot = new BarPlot($ydata);
$bplot->SetWidth(0.6);
$fcol='#440000';
$tcol='#FF9090';

$bplot->SetFillGradient($fcol,$tcol,GRAD_LEFT_REFLECTION);

// Set line weigth to 0 so that there are no border
// around each bar
$bplot->SetWeight(0);

$graph->Add($bplot);

// Create filled line plot
$lplot = new LinePlot($ydata2);
$lplot->SetFillColor('skyblue@0.5');
$lplot->SetColor('navy@0.7');
$lplot->SetBarCenter();

$lplot->mark->SetType(MARK_SQUARE);
$lplot->mark->SetColor('blue@0.5');
$lplot->mark->SetFillColor('lightblue');
$lplot->mark->SetSize(6);

$graph->Add($lplot);

// .. and finally send it back to the browser
$graph->Stroke();
?>
