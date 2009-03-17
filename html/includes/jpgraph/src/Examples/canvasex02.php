<?php
// $Id: canvasex02.php,v 1.1 2002/08/27 20:08:57 aditus Exp $
include "../jpgraph.php";
include "../jpgraph_canvas.php";

// Setup a basic canvas we can work 
$g = new CanvasGraph(400,200,'auto');
$g->SetMargin(5,11,6,11);
$g->SetShadow();
$g->SetMarginColor("teal");

// We need to stroke the plotarea and margin before we add the
// text since we otherwise would overwrite the text.
$g->InitFrame();

// Add a black line
$g->img->SetColor('black');
$g->img->Line(0,0,100,100);

// .. and a circle (x,y,diameter)
$g->img->Circle(100,100,50);

// .. and a filled circle (x,y,diameter)
$g->img->SetColor('red');
$g->img->FilledCircle(200,100,50);

// .. add a rectangle
$g->img->SetColor('green');
$g->img->FilledRectangle(10,10,50,50);

// .. add a filled rounded rectangle
$g->img->SetColor('green');
$g->img->FilledRoundedRectangle(300,30,350,80,10);
// .. with a darker border
$g->img->SetColor('darkgreen');
$g->img->RoundedRectangle(300,30,350,80,10);

// Stroke the graph
$g->Stroke();

?>

