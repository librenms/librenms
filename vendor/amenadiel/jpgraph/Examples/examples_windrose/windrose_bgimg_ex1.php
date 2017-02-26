<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
// Data can be specified using both ordinal idex of axis as well
// as the direction label
$data2 = array(
    'vsv' => array(12, 8, 2, 3),
    6 => array(5, 4, 4, 5, 4),
);

$se_CompassLbl = array('O', 'ONO', 'NO', 'NNO', 'N', 'NNV', 'NV', 'VNV', 'V', 'VSV', 'SV', 'SSV', 'S', 'SSO', 'SO', 'OSO');

// Create a new small windrose graph
$graph = new Graph\WindroseGraph(400, 400);
$graph->SetMargin(25, 25, 25, 25);
$graph->SetFrame();

$graph->title->Set('Example with background flag');
#$graph->title->SetFont(FF_VERA,FS_BOLD,14);

//$graph->SetBackgroundImage('bkgimg.jpg',BGIMG_FILLFRAME);
//$graph->SetBackgroundImageMix(90);
$graph->SetBackgroundCFlag(28, BGIMG_FILLFRAME, 15);

$wp2 = new Plot\WindrosePlot($data2);
$wp2->SetType(WINDROSE_TYPE16);
$wp2->SetSize(0.55);
$wp2->SetPos(0.5, 0.5);
$wp2->SetAntiAlias(false);

$wp2->SetFont(FF_ARIAL, FS_BOLD, 10);
$wp2->SetFontColor('black');

$wp2->SetCompassLabels($se_CompassLbl);
$wp2->legend->SetMargin(20, 5);

$wp2->scale->SetZFont(FF_ARIAL, FS_NORMAL, 8);
$wp2->scale->SetFont(FF_ARIAL, FS_NORMAL, 9);
$wp2->scale->SetLabelFillColor('white', 'white');

$wp2->SetRangeColors(array('green', 'yellow', 'red', 'brown'));

$graph->Add($wp2);
$graph->Stroke();
