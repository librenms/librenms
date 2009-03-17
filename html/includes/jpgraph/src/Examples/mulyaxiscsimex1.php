<?php
include_once ("../jpgraph.php");
include_once ("../jpgraph_line.php");


// Setup some dummy targets for the CSIM
$n = 5;
for($i=0; $i < $n; ++$i ) {
    $targ1[$i] = "#$i";
    $targ2[$i] = "#$i";
    $targ3[$i] = "#$i";
    $alts1[$i] = "val=%d";
    $alts2[$i] = "val=%d";
    $alts3[$i] = "val=%d";
}

// Some data for the points
$datay1 = array(3,10,4,1,6);
$datay2 = array(25,22,18,24,20);
$datay3 = array(89,70,92,77,96);

// Create a basic graph with some suitable margins
$graph = new Graph(500,250);
$graph->SetMargin(60,180,50,40);
$graph->SetMarginColor('white');
$graph->title->Set("Multi Y-axes with Image Map");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Setup the scales for all axes
$graph->SetScale("intlin");
$graph->SetYScale(0,'int');
$graph->SetYScale(1,'int');

// Standard Y-axis plot
$lp1 = new LinePlot($datay1);
$lp1->SetLegend('2001');
$lp1->mark->SetType(MARK_DIAMOND);
$lp1->mark->SetWidth(15);
$lp1->mark->SetFillColor('orange');
$lp1->SetCSIMTargets($targ1,$alts1);
$graph->yaxis->title->Set('Basic Rate');
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,10);
$graph->yaxis->title->SetColor('black');
$graph->Add($lp1);

// First multi Y-axis plot
$lp2 = new LinePlot($datay2);
$lp2->SetLegend('2002');
$lp2->mark->SetType(MARK_DIAMOND);
$lp2->mark->SetWidth(15);
$lp2->mark->SetFillColor('darkred');
$lp2->SetCSIMTargets($targ2,$alts2);
$graph->ynaxis[0]->SetColor('darkred');
$graph->ynaxis[0]->title->Set('Rate A');
$graph->ynaxis[0]->title->SetFont(FF_ARIAL,FS_BOLD,10);
$graph->ynaxis[0]->title->SetColor('darkred');
$graph->AddY(0,$lp2);

// Second multi Y-axis plot
$lp3 = new LinePlot($datay3);
$lp3->SetLegend('2003');
$lp3->mark->SetType(MARK_DIAMOND);
$lp3->mark->SetWidth(15);
$lp3->mark->SetFillColor('darkgreen');
$lp3->SetCSIMTargets($targ3,$alts3);
$graph->ynaxis[1]->SetColor('darkgreen');
$graph->ynaxis[1]->title->Set('Rate B');
$graph->ynaxis[1]->title->SetFont(FF_ARIAL,FS_BOLD,10);
$graph->ynaxis[1]->title->SetColor('darkgreen');
$graph->AddY(1,$lp3);

// Send back the HTML page which will call this script again
// to retrieve the image.
$graph->StrokeCSIM();
?>
