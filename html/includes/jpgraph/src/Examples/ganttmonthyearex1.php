<?php
// Gantt example
include ("../jpgraph.php");
include ("../jpgraph_gantt.php");

$graph = new GanttGraph();

$graph->title->Set("Only month & year scale");

// Setup some "very" nonstandard colors
$graph->SetMarginColor('lightgreen@0.8');
$graph->SetBox(true,'yellow:0.6',2);
$graph->SetFrame(true,'darkgreen',4);
$graph->scale->divider->SetColor('yellow:0.6');
$graph->scale->dividerh->SetColor('yellow:0.6');

// Explicitely set the date range 
// (Autoscaling will of course also work)
$graph->SetDateRange('2001-10-06','2002-4-01');

// Display month and year scale with the gridlines
$graph->ShowHeaders(GANTT_HMONTH | GANTT_HYEAR);
$graph->scale->month->grid->SetColor('gray');
$graph->scale->month->grid->Show(true);
$graph->scale->year->grid->SetColor('gray');
$graph->scale->year->grid->Show(true);

// Data for our example activities
$data = array(
	array(0,"Group 1  Johan", "2001-11-23","2002-03-1",FF_FONT1,FS_BOLD,8),
	array(1,"  Label 2", "2001-10-26","2001-11-16"));
	
// Create the bars and add them to the gantt chart
for($i=0; $i<count($data); ++$i) {
	$bar = new GanttBar($data[$i][0],$data[$i][1],$data[$i][2],$data[$i][3],"[50%]",10);
	if( count($data[$i])>4 )
		$bar->title->SetFont($data[$i][4],$data[$i][5],$data[$i][6]);
	$bar->SetPattern(BAND_RDIAG,"yellow");
	$bar->SetFillColor("red");
	$bar->progress->Set(0.5);
	$bar->progress->SetPattern(GANTT_SOLID,"darkgreen");
	$graph->Add($bar);
}

// Output the chart
$graph->Stroke();

?>


