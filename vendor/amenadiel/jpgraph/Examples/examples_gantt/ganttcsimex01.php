<?php // content="text/plain; charset=utf-8"
// Gantt example to create CSIM

require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_gantt.php');

$bar1 = new GanttBar(0,"Activity 1","2001-12-21","2002-01-20");
$bar1->SetCSIMTarget('#','Go back 1');
$bar1->title->SetCSIMTarget('#','Go back 1 (title)');
$bar2 = new GanttBar(1,"Activity 2","2002-01-03","2002-01-25");
$bar2->SetCSIMTarget('#','Go back 2');
$bar2->title->SetCSIMTarget('#','Go back 2 (title)');

$graph = new GanttGraph(500);
$graph->title->Set("Example with image map");
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
$graph->scale->week->SetFont(FF_FONT1);

$graph->Add(array($bar1,$bar2));

// And stroke
$graph->StrokeCSIM();


?>


