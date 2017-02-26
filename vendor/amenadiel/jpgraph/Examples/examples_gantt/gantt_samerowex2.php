<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_gantt.php');

$graph = new GanttGraph();
$graph->SetShadow();

// Add title and subtitle
$graph->title->Set("Activities on same row");
$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->subtitle->Set('Using break style');

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
$graph->SetLabelVMarginFactor(1); // 1=default value

// Format the bar for the first activity
// ($row,$title,$startdate,$enddate)
$activity1 = new GanttBar(0,"Activity 1","2001-12-21","2001-12-26","");

// Yellow diagonal line pattern on a red background
$activity1->SetPattern(BAND_RDIAG,"yellow");
$activity1->SetFillColor("red");

// Format the bar for the first activity
// ($row,$title,$startdate,$enddate)
$break1 = new GanttBar(0,'',"2001-12-27","2001-12-30","");
$break1->SetBreakStyle(true,'dotted',2);
$break1->SetColor('red');
$graph->Add($break1);

// Format the bar for the second activity
// ($row,$title,$startdate,$enddate)
$activity2 = new GanttBar(0,"","2001-12-31","2002-01-2","[BO]");

// ADjust font for caption
$activity2->caption->SetFont(FF_ARIAL,FS_BOLD);
$activity2->caption->SetColor("darkred");

// Yellow diagonal line pattern on a red background
$activity2->SetPattern(BAND_RDIAG,"yellow");
$activity2->SetFillColor("red");

// Finally add the bar to the graph
$graph->Add($activity1);
$graph->Add($activity2);

// ... and display it
$graph->Stroke();
?>
