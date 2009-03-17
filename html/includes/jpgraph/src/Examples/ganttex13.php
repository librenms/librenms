<?php
include ("../jpgraph.php");
include ("../jpgraph_gantt.php");

$graph = new GanttGraph(0,0,"auto");
$graph->SetBox();
$graph->SetShadow();

// Add title and subtitle
$graph->title->Set("Example of captions");
$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->subtitle->Set("(ganttex13.php)");

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
$graph->SetLabelVMarginFactor(1);

// Format the bar for the first activity
// ($row,$title,$startdate,$enddate)
$activity = new GanttBar(0,"Project","2001-12-21","2002-01-07","[ER,TR]");

// Yellow diagonal line pattern on a red background
$activity->SetPattern(BAND_RDIAG,"yellow");
$activity->SetFillColor("red");

// Set absolute height
$activity->SetHeight(10);


// Format the bar for the second activity
// ($row,$title,$startdate,$enddate)
$activity2 = new GanttBar(1,"Project","2001-12-21","2002-01-02","[BO,SW,JC]");

// ADjust font for caption
$activity2->caption->SetFont(FF_ARIAL,FS_BOLD);
$activity2->caption->SetColor("darkred");

// Yellow diagonal line pattern on a red background
$activity2->SetPattern(BAND_RDIAG,"yellow");
$activity2->SetFillColor("red");

// Set absolute height
$activity2->SetHeight(10);

// Finally add the bar to the graph
$graph->Add($activity);
$graph->Add($activity2);

// Add a vertical line
$vline = new GanttVLine("2001-12-24","Phase 1");
$vline->SetDayOffset(0.5);
//$graph->Add($vline);

// ... and display it
$graph->Stroke();
?>
