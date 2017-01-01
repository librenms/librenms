<?php
namespace Amenadiel\JpGraph\Plot;

//===================================================
// CLASS ErrorLinePlot
// Description: Combine a line and error plot
// THIS IS A DEPRECATED PLOT TYPE JUST KEPT FOR
// BACKWARD COMPATIBILITY
//===================================================
class ErrorLinePlot extends ErrorPlot
{
    public $line = null;
    //---------------
    // CONSTRUCTOR
    public function __construct($datay, $datax = false)
    {
        parent::__construct($datay, $datax);
        // Calculate line coordinates as the average of the error limits
        $n = count($datay);
        for ($i = 0; $i < $n; $i += 2) {
            $ly[] = ($datay[$i] + $datay[$i + 1]) / 2;
        }
        $this->line = new LinePlot($ly, $datax);
    }

    //---------------
    // PUBLIC METHODS
    public function Legend($graph)
    {
        if ($this->legend != "") {
            $graph->legend->Add($this->legend, $this->color);
        }

        $this->line->Legend($graph);
    }

    public function Stroke($img, $xscale, $yscale)
    {
        parent::Stroke($img, $xscale, $yscale);
        $this->line->Stroke($img, $xscale, $yscale);
    }
} // Class
