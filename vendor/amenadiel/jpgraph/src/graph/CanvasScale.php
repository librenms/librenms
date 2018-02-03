<?php
namespace Amenadiel\JpGraph\Graph;

//===================================================
// CLASS CanvasScale
// Description: Define a scale for canvas so we
// can abstract away with absolute pixels
//===================================================

class CanvasScale
{
    private $g;
    private $w;
    private $h;
    private $ixmin = 0;
    private $ixmax = 10;
    private $iymin = 0;
    private $iymax = 10;

    public function __construct($graph, $xmin = 0, $xmax = 10, $ymin = 0, $ymax = 10)
    {
        $this->g     = $graph;
        $this->w     = $graph->img->width;
        $this->h     = $graph->img->height;
        $this->ixmin = $xmin;
        $this->ixmax = $xmax;
        $this->iymin = $ymin;
        $this->iymax = $ymax;
    }

    public function Set($xmin = 0, $xmax = 10, $ymin = 0, $ymax = 10)
    {
        $this->ixmin = $xmin;
        $this->ixmax = $xmax;
        $this->iymin = $ymin;
        $this->iymax = $ymax;
    }

    public function Get()
    {
        return array($this->ixmin, $this->ixmax, $this->iymin, $this->iymax);
    }

    public function Translate($x, $y)
    {
        $xp = round(($x - $this->ixmin) / ($this->ixmax - $this->ixmin) * $this->w);
        $yp = round(($y - $this->iymin) / ($this->iymax - $this->iymin) * $this->h);
        return array($xp, $yp);
    }

    public function TranslateX($x)
    {
        $xp = round(($x - $this->ixmin) / ($this->ixmax - $this->ixmin) * $this->w);
        return $xp;
    }

    public function TranslateY($y)
    {
        $yp = round(($y - $this->iymin) / ($this->iymax - $this->iymin) * $this->h);
        return $yp;
    }
}
