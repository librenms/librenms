<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_table.php');

// Setup graph context
$graph = new CanvasGraph(165,90);

// Setup the basic table
$data = array( array(1,2,3,4),array(5,6,7,8), array(6,8,10,12));
$table = new GTextTable();
$table->Set($data);

// Setup overall table font
$table->SetFont(FF_ARIAL,FS_NORMAL,11);

// Setup font and color for row = 2
$table->SetRowFont(2,FF_ARIAL,FS_BOLD,11);
$table->SetRowFillColor(2,'orange@0.5');

// Setup minimum color width
$table->SetMinColWidth(40);

// Setup overall cell alignment for the table
$table->SetAlign('right');

// Setup overall table border
$table->SetBorder(0,'black');

// Setup overall table grid
$table->setGrid(0,'black');

// Set specific frid for row = 2
$table->SetRowGrid(2,1,'black',TGRID_DOUBLE2);

// Setup overall number format in all cells
$table->SetNumberFormat("%0.1f");

// Add table to the graph
$graph->Add($table);

// and send it back to the browser
$graph->Stroke();

?>

