<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_table.php');

// Create a canvas graph where the table can be added
$graph = new CanvasGraph(70,50);

// Setup the basic table
$data = array( array(1,2,3,4),array(5,6,7,8));
$table = new GTextTable();
$table->Set($data);

// Merge all cellsn in the rectangle with
// top left corner = (0,2) and bottom right = (1,3)
$table->MergeCells(0,2,1,3);

// Add the table to the graph
$graph->Add($table);

// ... and send back the table to the client
$graph->Stroke();

?>

