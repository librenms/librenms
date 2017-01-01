<?php
namespace Amenadiel\JpGraph\Util;

//===================================================
// CLASS GanttConstraint
// Just a structure to store all the values for a constraint
//===================================================
class GanttConstraint
{
    public $iConstrainRow;
    public $iConstrainType;
    public $iConstrainColor;
    public $iConstrainArrowSize;
    public $iConstrainArrowType;

    //---------------
    // CONSTRUCTOR
    public function __construct($aRow, $aType, $aColor, $aArrowSize, $aArrowType)
    {
        $this->iConstrainType = $aType;
        $this->iConstrainRow = $aRow;
        $this->iConstrainColor = $aColor;
        $this->iConstrainArrowSize = $aArrowSize;
        $this->iConstrainArrowType = $aArrowType;
    }
}
