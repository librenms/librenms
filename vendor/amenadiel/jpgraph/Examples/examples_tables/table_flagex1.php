<?php
include '../jpgraph.php';
include '../jpgraph_canvas.php';
include '../jpgraph_table.php';
include '../jpgraph_iconplot.php';
include '../jpgraph_flags.php';

// Setup a basic canvas to use as graph to add the table
$graph = new CanvasGraph(500,200);

// Setup the basic table
$data = array(
    array('Areas'),
    array(''),
    array('','USA','UK','France','Denmark','Iceland','Canada'),
    array('Feb',13,17,15,8,3,9),
    array('Mar',34,35,26,20,22,16),
    array('Apr',41,43,49,45,51,47),
    array('Sum:',88,95,90,73,76,72));

$countries = array('united states','united kingdom','french republic','denmark','iceland','canada');

// Create a basic table and default fonr
$table = new GTextTable();
$table->Set($data);
$table->SetFont(FF_TIMES,FS_NORMAL,11);

// Adjust the font for row 0 and 6
$table->SetColFont(0,FF_ARIAL,FS_BOLD,11);
$table->SetRowFont(6,FF_TIMES,FS_BOLD,12);

// Set the minimum heigth/width
$table->SetMinRowHeight(2,10);
$table->SetMinColWidth(70);

// Add some padding (in pixels)
$table->SetRowPadding(2,0);
$table->SetRowGrid(6,1,'darkgray',TGRID_DOUBLE2);

// Setup the grid
$table->SetGrid(0);
$table->SetRowGrid(6,1,'black',TGRID_DOUBLE2);

// Merge all cells in row 0
$table->MergeRow(0);

// Set aligns
$table->SetAlign(3,0,6,6,'right');
$table->SetRowAlign(1,'center');
$table->SetRowAlign(2,'center');

// Set background colors
$table->SetRowFillColor(0,'lightgray@0.5');
$table->SetColFillColor(0,'lightgray@0.5');

// Add the country flags in row 1
$n = count($countries);
for($i=0; $i < $n; ++$i ) {
    $table->SetCellCountryFlag(1,$i+1,$countries[$i],0.5);
    $table->SetCellImageConstrain(1,$i+1,TIMG_HEIGHT,20);
}

// Add the table to the graph
$graph->Add($table);

// Send back the table graph to the client
$graph->Stroke();

?>

