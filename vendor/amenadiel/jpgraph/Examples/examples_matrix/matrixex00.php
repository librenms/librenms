<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_matrix.php');

$data = array(
array(0,1,2,3,4,5,6,7,8,9,10),
array(10,9,8,7,6,5,4,3,2,1,0),
array(0,1,2,3,4,5,6,7,8,9,10),
array(10,9,8,17,6,5,4,3,2,1,0),
array(0,1,2,3,4,4,9,7,8,9,10),
array(8,1,2,3,4,8,3,7,8,9,10),
array(10,3,5,7,6,5,4,3,12,1,0),
array(10,9,8,7,6,5,4,3,2,1,0),
);

doMeshInterpolate($data,4);

$graph = new MatrixGraph(850,580);
$graph->title->Set('Matrix example 00');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,14);

//$graph->SetColor('darkgreen@0.8');

$mp = array();
$n = 5;
for($i=0; $i < $n; ++$i){
    $mp[$i] = new MatrixPlot($data);
    $mp[$i]->colormap->SetMap($i);
    if( $i < 2 )
        $mp[$i]->SetSize(0.35);
    else
        $mp[$i]->SetSize(0.21);    
}

$hor1 = new LayoutHor(array($mp[0],$mp[1]));
$hor2 = new LayoutHor(array($mp[2],$mp[3],$mp[4]));
$vert = new LayoutVert(array($hor1,$hor2));
$vert->SetCenterPos(0.45,0.5);

//$mp = new MatrixPlot($data);
//$mp->colormap->SetMap(2);
//$mp->SetCenterPos(0.5, 0.45);
//$mp->SetLegendLayout(0);
//$mp->SetSize(0.6);
//$mp->legend->Show(false);
//$mp->SetModuleSize(5,5);

//$mp->legend->SetModuleSize(20,4);
//$mp->legend->SetSize(20,0.5);

//$t = new Text('A text string',10,10);
//$graph->Add($t);

//$graph->Add($mp);

$graph->Add($vert);
$graph->Stroke();

?>
