<?php
// Gantt example to create CSIM using CreateSimple()

include ("../jpgraph.php");
include ("../jpgraph_gantt.php");

$data = array(
  array(0,ACTYPE_GROUP,    "Phase 1",        "2001-10-26","2001-11-23",'',
	'#1','Go home'),
  array(1,ACTYPE_NORMAL,   "  Label 2",      "2001-10-26","2001-11-16",'ab,cd',
	'#2','Go home'),
  array(2,ACTYPE_NORMAL,   "  Label 3",      "2001-11-20","2001-11-22",'ek',
	'#3','Go home'),
  array(3,ACTYPE_MILESTONE,"  Phase 1 Done", "2001-11-23",'M2',
	'#4','Go home') );

// The constrains between the activities
$constrains = array(array(1,2,CONSTRAIN_ENDSTART),
		    array(2,3,CONSTRAIN_STARTSTART));

$progress = array(array(1,0.4));

$graph = new GanttGraph(500);
$graph->title->Set("Example with image map");
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
$graph->scale->week->SetFont(FF_FONT1);

$graph->CreateSimple($data,$constrains,$progress);

// Add the specified activities
//SetupSimpleGantt($graph,$data,$constrains,$progress);

// And stroke
$graph->StrokeCSIM();


?>


