<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';
require_once 'jpgraph/jpgraph_date.php';
require_once 'jpgraph/jpgraph_mgraph.php';

// Setup some fake data to simulate some wind speed and direction

DEFINE('NDATAPOINTS', 280);
DEFINE('SAMPLERATE', 300);

$start = time();
$end   = $start + NDATAPOINTS * SAMPLERATE;
$xdata = array();

$data_winddirection[0] = rand(100, 200);
$data_windspeed[0]     = rand(7, 10);
$data_windtemp[0]      = rand(5, 20);

for ($i = 0; $i < NDATAPOINTS - 1; ++$i) {
    $data_winddirection[$i + 1] = $data_winddirection[$i] + rand(-4, 4);
    if ($data_winddirection[$i + 1] < 0 || $data_winddirection[$i + 1] > 359) {
        $data_winddirection[$i + 1] = 0;
    }

    $data_windspeed[$i + 1] = $data_windspeed[$i] + rand(-2, 2);
    if ($data_windspeed[$i + 1] < 0) {
        $data_windspeed[$i + 1] = 0;
    }

    $data_windtemp[$i + 1] = $data_windtemp[$i] + rand(-1.5, 1.5);

    $xdata[$i] = $start + $i * SAMPLERATE;
}
$xdata[$i] = $start + $i * SAMPLERATE;

//DEFINE('BKG_COLOR','lightgray:1.7');
DEFINE('BKG_COLOR', 'green:1.98');
DEFINE('WIND_HEIGHT', 800);
DEFINE('WIND_WIDTH', 250);

//------------------------------------------------------------------
// Setup the Wind direction graph
//------------------------------------------------------------------
$graph = new Graph\Graph(WIND_WIDTH, WIND_HEIGHT);
$graph->SetMarginColor(BKG_COLOR);
$graph->SetScale('datlin', 0, 360);
$graph->Set90AndMargin(50, 10, 70, 30);
$graph->SetFrame(true, 'white', 0);
$graph->SetBox();

$graph->title->Set('Wind direction');
$graph->title->SetColor('blue');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);
$graph->title->SetMargin(5);

$graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL, 9);
$graph->xaxis->scale->SetDateFormat('H:i');
$graph->xgrid->Show();

$graph->yaxis->SetLabelAngle(90);
$graph->yaxis->SetColor('blue');
$graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 9);
$graph->yaxis->SetLabelMargin(0);
$graph->yaxis->scale->SetAutoMin(0);

$line = new Plot\LinePlot($data_winddirection, $xdata);
$line->SetStepStyle();
$line->SetColor('blue');

$graph->Add($line);

//------------------------------------------------------------------
// Setup the wind speed graph
//------------------------------------------------------------------
$graph2 = new Graph\Graph(WIND_WIDTH - 30, WIND_HEIGHT);
$graph2->SetScale('datlin');
$graph2->Set90AndMargin(5, 20, 70, 30);
$graph2->SetMarginColor(BKG_COLOR);
$graph2->SetFrame(true, 'white', 0);
$graph2->SetBox();

$graph2->title->Set('Windspeed');
$graph2->title->SetColor('red');
$graph2->title->SetFont(FF_ARIAL, FS_BOLD, 14);
$graph2->title->SetMargin(5);

$graph2->xaxis->HideLabels();
$graph2->xgrid->Show();

$graph2->yaxis->SetLabelAngle(90);
$graph2->yaxis->SetColor('red');
$graph2->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 9);
$graph2->yaxis->SetLabelMargin(0);
$graph2->yaxis->scale->SetAutoMin(0);

$line2 = new Plot\LinePlot($data_windspeed, $xdata);
$line2->SetStepStyle();
$line2->SetColor('red');

$graph2->Add($line2);

//------------------------------------------------------------------
// Setup the wind temp graph
//------------------------------------------------------------------
$graph3 = new Graph\Graph(WIND_WIDTH - 30, WIND_HEIGHT);
$graph3->SetScale('datlin');
$graph3->Set90AndMargin(5, 20, 70, 30);
$graph3->SetMarginColor(BKG_COLOR);
$graph3->SetFrame(true, 'white', 0);
$graph3->SetBox();

$graph3->title->Set('Temperature');
$graph3->title->SetColor('black');
$graph3->title->SetFont(FF_ARIAL, FS_BOLD, 14);
$graph3->title->SetMargin(5);

$graph3->xaxis->HideLabels();
$graph3->xgrid->Show();

$graph3->yaxis->SetLabelAngle(90);
$graph3->yaxis->SetColor('black');
$graph3->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 9);
$graph3->yaxis->SetLabelMargin(0);
$graph3->yaxis->scale->SetAutoMin(-10);

$line3 = new Plot\LinePlot($data_windtemp, $xdata);
$line3->SetStepStyle();
$line3->SetColor('black');

$graph3->Add($line3);

//-----------------------
// Create a multigraph
//----------------------
$mgraph = new MGraph();
$mgraph->SetMargin(2, 2, 2, 2);
$mgraph->SetFrame(true, 'darkgray', 2);
$mgraph->SetFillColor(BKG_COLOR);
$mgraph->Add($graph, 0, 50);
$mgraph->Add($graph2, 250, 50);
$mgraph->Add($graph3, 460, 50);
$mgraph->title->Set('Climate diagram 12 March 2009');
$mgraph->title->SetFont(FF_ARIAL, FS_BOLD, 20);
$mgraph->title->SetMargin(8);
$mgraph->Stroke();
