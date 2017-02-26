<?php // content="text/plain; charset=utf-8"
// $Id: canvasex01.php,v 1.3 2002/10/23 08:17:23 aditus Exp $
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');

// Setup a basic canvas we can work 
$g = new CanvasGraph(400,300,'auto');
$g->SetMargin(5,11,6,11);
$g->SetShadow();
$g->SetMarginColor("teal");

// We need to stroke the plotarea and margin before we add the
// text since we otherwise would overwrite the text.
$g->InitFrame();

// Draw a text box in the middle
$txt="This\nis\na TEXT!!!";
$t = new Text($txt,200,10);
$t->SetFont(FF_ARIAL,FS_BOLD,40);

// How should the text box interpret the coordinates?
$t->Align('center','top');

// How should the paragraph be aligned?
$t->ParagraphAlign('center');

// Add a box around the text, white fill, black border and gray shadow
$t->SetBox("white","black","gray");

// Stroke the text
$t->Stroke($g->img);

// Stroke the graph
$g->Stroke();

?>

