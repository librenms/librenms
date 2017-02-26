<?php
namespace Amenadiel\JpGraph\Util;

/*=======================================================================
// File:        JPGRAPH_UTILS.INC
// Description: Collection of non-essential "nice to have" utilities
// Created:     2005-11-20
// Ver:         $Id: jpgraph_utils.inc.php 1777 2009-08-23 17:34:36Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================
 */

//===================================================
// CLASS FuncGenerator
// Description: Utility class to help generate data for function plots.
// The class supports both parametric and regular functions.
//===================================================
class FuncGenerator
{
    private $iFunc = '', $iXFunc = '', $iMin, $iMax, $iStepSize;

    public function __construct($aFunc, $aXFunc = '')
    {
        $this->iFunc = $aFunc;
        $this->iXFunc = $aXFunc;
    }

    public function E($aXMin, $aXMax, $aSteps = 50)
    {
        $this->iMin = $aXMin;
        $this->iMax = $aXMax;
        $this->iStepSize = ($aXMax - $aXMin) / $aSteps;

        if ($this->iXFunc != '') {
            $t = 'for($i=' . $aXMin . '; $i<=' . $aXMax . '; $i += ' . $this->iStepSize . ') {$ya[]=' . $this->iFunc . ';$xa[]=' . $this->iXFunc . ';}';
        } elseif ($this->iFunc != '') {
            $t = 'for($x=' . $aXMin . '; $x<=' . $aXMax . '; $x += ' . $this->iStepSize . ') {$ya[]=' . $this->iFunc . ';$xa[]=$x;} $x=' . $aXMax . ';$ya[]=' . $this->iFunc . ';$xa[]=$x;';
        } else {
            JpGraphError::RaiseL(24001);
        }
        //('FuncGenerator : No function specified. ');

        @eval($t);

        // If there is an error in the function specifcation this is the only
        // way we can discover that.
        if (empty($xa) || empty($ya)) {
            JpGraphError::RaiseL(24002);
        }
        //('FuncGenerator : Syntax error in function specification ');

        return array($xa, $ya);
    }
}
