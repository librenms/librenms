<?php
namespace Amenadiel\JpGraph\Plot;

//===================================================
// CLASS BoxPlot
//===================================================
class BoxPlot extends StockPlot
{
    private $iPColor = 'black';
    private $iNColor = 'white';

    public function __construct($datay, $datax = false)
    {
        $this->iTupleSize = 5;
        parent::__construct($datay, $datax);
    }

    public function SetMedianColor($aPos, $aNeg)
    {
        $this->iPColor = $aPos;
        $this->iNColor = $aNeg;
    }

    public function ModBox($img, $xscale, $yscale, $i, $xl, $xr, $neg)
    {
        if ($neg) {
            $img->SetColor($this->iNColor);
        } else {
            $img->SetColor($this->iPColor);
        }

        $y = $yscale->Translate($this->coords[0][$i * 5 + 4]);
        $img->Line($xl, $y, $xr, $y);
    }
}
