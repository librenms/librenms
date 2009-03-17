<?php
// $Id: canvasbezierex1.php,v 1.1 2002/10/05 21:04:28 aditus Exp $
include "../jpgraph.php";
include "../jpgraph_canvas.php";
include "../jpgraph_canvtools.php";

// Setup canvas graph
$g = new CanvasGraph(400,300);
$scale = new CanvasScale($g);
$shape = new Shape($g,$scale);

$g->title->Set('Bezier line with control points');

// Setup control point for bezier
$p = array(3,6,
	   6,9,
	   5,3,
	   7,4);

// Visualize control points
$shape->SetColor('blue');
$shape->Line($p[0],$p[1],$p[2],$p[3]);
$shape->FilledCircle($p[2],$p[3],-6);

$shape->SetColor('red');
$shape->Line($p[4],$p[5],$p[6],$p[7]);
$shape->FilledCircle($p[4],$p[5],-6);

// Draw bezier
$shape->SetColor('black');
$shape->Bezier($p);

// Frame it with a square
$shape->SetColor('navy');
$shape->Rectangle(0.5,2,9.5,9.5);

// ... and stroke it
$g->Stroke();
?>

