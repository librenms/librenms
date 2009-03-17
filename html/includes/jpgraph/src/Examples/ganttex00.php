<?php
include ("../jpgraph.php");
include ("../jpgraph_gantt.php");

// A new graph with automatic size
$graph = new GanttGraph(0,0,"auto");

//  A new activity on row '0'
$activity = new GanttBar(0,"Project","2001-12-21","2002-01-20");
$graph->Add($activity);

// Display the Gantt chart
$graph->Stroke();
?>
