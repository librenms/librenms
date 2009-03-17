<?php
// Gantt example 30
// $Id: ganttex30.php,v 1.4 2003/05/30 20:12:43 aditus Exp $
include ("../jpgraph.php");
include ("../jpgraph_gantt.php");

// Standard calls to create a new graph
$graph = new GanttGraph(0,0,"auto");
$graph->SetShadow();
$graph->SetBox();

// Titles for chart
$graph->title->Set("General conversion plan");
$graph->subtitle->Set("(Revision: 2001-11-18)");
$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);

// For illustration we enable all headers. 
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);

// For the week we choose to show the start date of the week
// the default is to show week number (according to ISO 8601)
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

// Change the scale font 
$graph->scale->week->SetFont(FF_FONT0);
$graph->scale->year->SetFont(FF_ARIAL,FS_BOLD,12);


// Setup some data for the gantt bars
$data = array(
	array(0,"Group 1", "2001-10-29","2001-11-27",FF_FONT1,FS_BOLD,8),
	array(1,"  Label 2", "2001-11-8","2001-12-14"),
	array(2,"  Label 3", "2001-11-01","2001-11-8"),
	array(4,"Group 2", "2001-11-07","2001-12-19",FF_FONT1,FS_BOLD,8),
	array(5,"  Label 4", "2001-11-8","2001-12-19"),
	array(6,"  Label 5", "2001-11-01","2001-11-8")
	);

for($i=0; $i<count($data); ++$i) {
	$bar = new GanttBar($data[$i][0],$data[$i][1],$data[$i][2],$data[$i][3],"[50%]",0.5);
	if( count($data[$i])>4 )
		$bar->title->SetFont($data[$i][4],$data[$i][5],$data[$i][6]);
		
	// If you like each bar can have a shadow
	// $bar->SetShadow(true,"darkgray");	
	
	// For illustration lets make each bar be red with yellow diagonal stripes
	$bar->SetPattern(BAND_RDIAG,"yellow");
	$bar->SetFillColor("red");
	
	// To indicate progress each bar can have a smaller bar within
	// For illustrative purpose just set the progress to 50% for each bar
	$bar->progress->Set(0.5);
	
	// Each bar may also have optional left and right plot marks
	// As illustration lets put a filled circle with a number at the end
	// of each bar
	$bar->rightMark->SetType(MARK_FILLEDCIRCLE);
	$bar->rightMark->SetFillColor("red");
	$bar->rightMark->SetColor("red");
	$bar->rightMark->SetWidth(10);
	
	// Title for the mark
	$bar->rightMark->title->Set("".$i+1);
	$bar->rightMark->title->SetColor("white");
	$bar->rightMark->title->SetFont(FF_ARIAL,FS_BOLD,10);
	$bar->rightMark->Show();
	
	// ... and add the bar to the gantt chart
	$graph->Add($bar);
}

// Create a milestone mark
$ms = new MileStone(7,"M5","2001-12-10","10/12");
$ms->title->SetFont(FF_FONT1,FS_BOLD);
$graph->Add($ms);

// Create a vertical line to emphasize the milestone
$vl = new GanttVLine("2001-12-10","Phase 1","darkred");
$vl->SetDayOffset(0.5);	// Center the line in the day
$graph->Add($vl);

// Output the graph
$graph->Stroke();

// EOF
?>