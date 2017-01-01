<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_gantt.php');

$graph = new GanttGraph();
$graph->SetShadow();

// Add title and subtitle
$graph->title->Set("Zooming a graph");
$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->subtitle->Set("(zoom=0.7)");

// Show day, week and month scale
$graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);

// Instead of week number show the date for the first day in the week
// on the week scale
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

// Make the week scale font smaller than the default
$graph->scale->week->SetFont(FF_FONT0);

// Use the short name of the month together with a 2 digit year
// on the month scale
$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
$graph->scale->month->SetFontColor("white");
$graph->scale->month->SetBackgroundColor("blue");

// 0 % vertical label margin
$graph->SetLabelVMarginFactor(1.0); // 1=default value

// Set zoom factor
$graph->SetZoomFactor(0.7);

// Format the bar for the first activity
// ($row,$title,$startdate,$enddate)
$activity1 = new GanttBar(0,"Activity 1","2001-12-21","2002-01-07","[ER,TR]");

// Yellow diagonal line pattern on a red background
$activity1->SetPattern(BAND_RDIAG,"yellow");
$activity1->SetFillColor("red");

// Set absolute height of activity
$activity1->SetHeight(16);

// Format the bar for the second activity
// ($row,$title,$startdate,$enddate)
$activity2 = new GanttBar(1,"Activity 2","2001-12-21","2002-01-01","[BO,SW,JC]");

// ADjust font for caption
$activity2->caption->SetFont(FF_ARIAL,FS_BOLD);
$activity2->caption->SetColor("darkred");

// Yellow diagonal line pattern on a red background
$activity2->SetPattern(BAND_RDIAG,"yellow");
$activity2->SetFillColor("red");

// Set absolute height of activity
$activity2->SetHeight(16);

// Finally add the bar to the graph
$graph->Add($activity1);
$graph->Add($activity2);

// ... and display it
$graph->Stroke();
?>
