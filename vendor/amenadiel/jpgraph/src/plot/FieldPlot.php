<?php
namespace Amenadiel\JpGraph\Plot;

//===================================================
// CLASS FieldPlot
// Description: Render a field plot
//===================================================
class FieldPlot extends Plot
{
    public $arrow      = '';
    private $iAngles   = array();
    private $iCallback = '';

    public function __construct($datay, $datax, $angles)
    {
        if ((count($datax) != count($datay))) {
            Util\JpGraphError::RaiseL(20001);
        }
        //("Fieldplots must have equal number of X and Y points.");
        if ((count($datax) != count($angles))) {
            Util\JpGraphError::RaiseL(20002);
        }
        //("Fieldplots must have an angle specified for each X and Y points.");

        $this->iAngles = $angles;

        parent::__construct($datay, $datax);
        $this->value->SetAlign('center', 'center');
        $this->value->SetMargin(15);

        $this->arrow = new FieldArrow();
    }

    public function SetCallback($aFunc)
    {
        $this->iCallback = $aFunc;
    }

    public function Stroke($img, $xscale, $yscale)
    {

        // Remeber base color and size
        $bc  = $this->arrow->iColor;
        $bs  = $this->arrow->iSize;
        $bas = $this->arrow->iArrowSize;

        for ($i = 0; $i < $this->numpoints; ++$i) {
            // Skip null values
            if ($this->coords[0][$i] === "") {
                continue;
            }

            $f = $this->iCallback;
            if ($f != "") {
                list($cc, $cs, $cas) = call_user_func($f, $this->coords[1][$i], $this->coords[0][$i], $this->iAngles[$i]);
                // Fall back on global data if the callback isn't set
                if ($cc == "") {
                    $cc = $bc;
                }

                if ($cs == "") {
                    $cs = $bs;
                }

                if ($cas == "") {
                    $cas = $bas;
                }

                $this->arrow->SetColor($cc);
                $this->arrow->SetSize($cs, $cas);
            }

            $xt = $xscale->Translate($this->coords[1][$i]);
            $yt = $yscale->Translate($this->coords[0][$i]);

            $this->arrow->Stroke($img, $xt, $yt, $this->iAngles[$i]);
            $this->value->Stroke($img, $this->coords[0][$i], $xt, $yt);
        }
    }

    // Framework function
    public function Legend($aGraph)
    {
        if ($this->legend != "") {
            $aGraph->legend->Add($this->legend, $this->mark->fill_color, $this->mark, 0,
                $this->legendcsimtarget, $this->legendcsimalt, $this->legendcsimwintarget);
        }
    }
}
