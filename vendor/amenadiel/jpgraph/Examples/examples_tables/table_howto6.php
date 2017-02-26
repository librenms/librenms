<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_table.php');

// Create a canvas graph where the table can be added
$graph = new CanvasGraph(150,60);

// Setup the basic table
$data = array( array(1,2,3,4),array(5,6,7,8));
$table = new GTextTable();
$table->Set($data);

// Merge all cells in row 0
$table->MergeRow(0);

// Setup font and color
$table->SetCellFont(0,0,FF_ARIAL,FS_BOLD,14);
$table->SetRowFillColor(0,'orange@0.5');
$table->SetRowColor(0,'darkred');

// Setup the minimum width of all columns
$table->SetMinColWidth(35);

// Add table to the graph
$graph->Add($table);

// ... send it back to the client
$graph->Stroke();

?>

