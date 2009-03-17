<?php
// $Id: listfontsex1.php,v 1.3 2002/10/25 22:44:15 aditus Exp $
include "../jpgraph.php";
include "../jpgraph_canvas.php";
include "../jpgraph_canvtools.php";


$g = new CanvasGraph(550,450,'auto');
$scale = new CanvasScale($g);
$scale->Set(0,27,0,53);
$g->SetMargin(5,6,5,6);
$g->SetColor('white');
$g->SetMarginColor("teal");
$g->InitFrame();


$t = new CanvasRectangleText();
$t->SetFillColor('lightgreen');
$t->SetFontColor('navy');
$t->SetFont(FF_ARIAL,FS_NORMAL,16);
$t->Set("\n\n\n\n\n\n\n\n\n\n\nTTF Fonts",0.5,19,26,32);
$t->Stroke($g->img,$scale);


$t = new CanvasRectangleText();

$t->SetFillColor('');
$t->SetFontColor('black');
$t->SetColor('');
$t->SetShadow('');
$t->SetFont(FF_ARIAL,FS_BOLD,18);
$t->Set('Family',1,1,8);
$t->Stroke($g->img,$scale);

$t->Set('Italic style',9,1,8);
$t->Stroke($g->img,$scale);

$t->Set('Bold style',17.5,1,8);
$t->Stroke($g->img,$scale);


$t->SetFillColor('yellow');
$t->SetFontColor('black');
$t->SetColor('black');
$t->SetShadow('gray');

$r=6;$c=1;$w=7.5;$h=3.5;

$fonts=array(
    array("Font 0",FF_FONT0,FS_NORMAL),
    array("",FF_FONT0,FS_ITALIC),
    array("",FF_FONT0,FS_BOLD),

    array("Font 1",FF_FONT1,FS_NORMAL),
    array("",FF_FONT1,FS_ITALIC),
    array("Font 1 bold",FF_FONT1,FS_BOLD),

    array("Font 2",FF_FONT2,FS_NORMAL),
    array("",FF_FONT2,FS_ITALIC),
    array("Font 2 bold",FF_FONT2,FS_BOLD),

    array("Arial",FF_ARIAL,FS_NORMAL),
    array("Arial italic",FF_ARIAL,FS_ITALIC),
    array("Arial bold",FF_ARIAL,FS_BOLD),

    array("Verdana",FF_VERDANA,FS_NORMAL),
    array("Verdana italic",FF_VERDANA,FS_ITALIC),
    array("Verdana bold",FF_VERDANA,FS_BOLD),


    array("Trebuche",FF_TREBUCHE,FS_NORMAL),
    array("Trebuche italic",FF_TREBUCHE,FS_ITALIC),
    array("Trebuche bold",FF_TREBUCHE,FS_BOLD),

    array("Georgia",FF_GEORGIA,FS_NORMAL),
    array("Georgia italic",FF_GEORGIA,FS_ITALIC),
    array("Georgia bold",FF_GEORGIA,FS_BOLD),

    array("Comic",FF_COMIC,FS_NORMAL),
    array("",FF_COMIC,FS_ITALIC),
    array("Comic bold",FF_COMIC,FS_BOLD),

    array("Courier",FF_COURIER,FS_NORMAL),
    array("Courier italic",FF_COURIER,FS_ITALIC),
    array("Courier bold",FF_COURIER,FS_BOLD),


    array("Times normal",FF_TIMES,FS_NORMAL),
    array("Times italic",FF_TIMES,FS_ITALIC),
    array("Times bold italic",FF_TIMES,FS_BOLDITALIC),
    );


$n=count($fonts);

for( $i=0; $i < $n; ++$i ) {
    
    if( $i==9 ) $r += 3;

    if( $fonts[$i][0] ) {
	$t->SetTxt($fonts[$i][0]);
	$t->SetPos($c,$r,$w,$h);
	$t->SetFont($fonts[$i][1],$fonts[$i][2],13);
	$t->Stroke($g->img,$scale);
    }

    $c += $w+1;
    if( $c > 30-$w-2 ) {
	$c = 1;
	$r += 4;
    }

}

$g->Stroke();
?>

