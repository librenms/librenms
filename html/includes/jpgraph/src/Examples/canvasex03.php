<?php
// $Id: canvasex03.php,v 1.1 2002/08/27 20:08:57 aditus Exp $
include "../jpgraph.php";
include "../jpgraph_canvas.php";
include "../jpgraph_canvtools.php";

// Define work space
$xmax=20;
$ymax=20;

// Setup a basic canvas we can work 
$g = new CanvasGraph(400,200,'auto');
$g->SetMargin(5,11,6,11);
$g->SetShadow();
$g->SetMarginColor("teal");

// We need to stroke the plotarea and margin before we add the
// text since we otherwise would overwrite the text.
$g->InitFrame();

// Create a new scale
$scale = new CanvasScale($g);
$scale->Set(0,$xmax,0,$ymax);

// The shape class is wrapper around the Imgae class which translates
// the coordinates for us
$shape = new Shape($g,$scale);
$shape->SetColor('black');


// Add a black line
$shape->SetColor('black');
$shape->Line(0,0,20,20);

// .. and a circle (x,y,diameter)
$shape->Circle(5,14,2);

// .. and a filled circle (x,y,diameter)
$shape->SetColor('red');
$shape->FilledCircle(11,8,3);

// .. add a rectangle
$shape->SetColor('green');
$shape->FilledRectangle(15,8,19,14);

// .. add a filled rounded rectangle
$shape->SetColor('green');
$shape->FilledRoundedRectangle(2,3,8,6);
// .. with a darker border
$shape->SetColor('darkgreen');
$shape->RoundedRectangle(2,3,8,6);


// Stroke the graph
$g->Stroke();

?>

