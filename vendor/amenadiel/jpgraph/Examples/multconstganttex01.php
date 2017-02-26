<?php // content="text/plain; charset=utf-8" 
// Gantt example 
require_once ('jpgraph/jpgraph.php'); 
require_once ('jpgraph/jpgraph_gantt.php'); 

// Create the basic graph 
$graph = new GanttGraph(); 
$graph->title->Set("Example with multiple constrains"); 

$bar1 = new GanttBar(0, "Label 1", "2003-06-08", "2003-06-12"); 
$bar2 = new GanttBar(1, "Label 2", "2003-06-16", "2003-06-19"); 
$bar3 = new GanttBar(2, "Label 3", "2003-06-15", "2003-06-21"); 

//create constraints 
$bar1->SetConstrain(1, CONSTRAIN_ENDSTART); 
$bar1->SetConstrain(2, CONSTRAIN_ENDSTART); 

// Setup scale 
$graph->ShowHeaders(/*GANTT_HYEAR | GANTT_HMONTH |*/ GANTT_HDAY | GANTT_HWEEK); 
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAYWNBR); 

// Add the specified activities 
$graph->Add($bar1); 
$graph->Add($bar2); 
$graph->Add($bar3); 

// .. and stroke the graph 
$graph->Stroke(); 

?>
