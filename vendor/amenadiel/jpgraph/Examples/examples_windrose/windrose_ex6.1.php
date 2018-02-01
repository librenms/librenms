<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

$data = array(
    '10'    => array(1, 1, 2.5, 4),
    '32.0'  => array(3, 4, 1, 4),
    '120.5' => array(2, 3, 4, 4, 3, 2, 1),
    '223.2' => array(2, 4, 1, 2, 2),
    '285.7' => array(2, 2, 1, 2, 4, 2, 1, 1),
);

// This file is encode din utf-8. The two Kanji characters roughly means
// 中 = Chinese
// 文 = Sentences
$ctxt = '中文';

// Specify text for direction labels
$labels = array(
    '120.5' => $ctxt,
    '232.2' => "Reference\n#13 Ver:2");

// Range colors to be used
$rangeColors = array('khaki', 'yellow', 'orange', 'orange:0.7', 'brown', 'darkred', 'black');

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 450);

// Setup title
$graph->title->Set('Using chinese charecters');
#$graph->title->SetFont(FF_VERDANA,FS_BOLD,12);
$graph->title->SetColor('navy');
$graph->subtitle->Set('(Free type plot)');
#$graph->subtitle->SetFont(FF_VERDANA,FS_ITALIC,10);
$graph->subtitle->SetColor('navy');

// Create the windrose plot.
$wp = new Plot\WindrosePlot($data);

// Setup a free plot
$wp->SetType(WINDROSE_TYPEFREE);

// Setup labels
$wp->SetLabels($labels);
$wp->SetLabelPosition(LBLPOSITION_CENTER);
$wp->SetLabelMargin(30);

// Setup the colors for the ranges
$wp->SetRangeColors($rangeColors);

// Adjust the font and font color for scale labels
#$wp->scale->SetFont(FF_ARIAL,FS_NORMAL,9);

// Set the diameter and position for plot
#$wp->SetSize(240);
$wp->SetSize(200);
$wp->SetZCircleSize(30);
$wp->SetPos(0.5, 0.5);

// Adjust the font and font color for compass directions
#$wp->SetFont(FF_CHINESE,FS_NORMAL,12);
$wp->SetFontColor('darkgreen');

// Adjust grid colors
$wp->SetGridColor('darkgreen@0.7', 'blue');

// Add (m/s) text to legend
$wp->legend->SetText('(m/s)');
$wp->legend->SetTFontColor('blue');

// Set legend label font color
$wp->legend->SetLFontColor('orange:0.7');
#$wp->legend->SetLFont(FF_ARIAL,FS_ITALIC,8);

// Display legend values with no decimals
$wp->legend->SetFormat('%d');

// Set the circle font to use chinse character set
// Note: When FF_CHINESE is used the input charectr data are
// assumed to already be in utf-8 encoding
#$wp->legend->SetCFont(FF_CHINESE,FS_NORMAL,14);
$wp->legend->SetCircleText($ctxt);
$wp->legend->SetCFontColor('red');

// Add plot to graph and send back to client
$graph->Add($wp);
$graph->Stroke();
