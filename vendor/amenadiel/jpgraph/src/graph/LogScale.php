<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

/*=======================================================================
// File:        JPGRAPH_LOG.PHP
// Description: Log scale plot extension for JpGraph
// Created:     2001-01-08
// Ver:         $Id: jpgraph_log.php 1106 2009-02-22 20:16:35Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================
 */

DEFINE('LOGLABELS_PLAIN', 0);
DEFINE('LOGLABELS_MAGNITUDE', 1);

//===================================================
// CLASS LogScale
// Description: Logarithmic scale between world and screen
//===================================================
class LogScale extends LinearScale
{
    //---------------
    // CONSTRUCTOR

    // Log scale is specified using the log of min and max
    public function __construct($min, $max, $type = "y")
    {
        parent::__construct($min, $max, $type);
        $this->ticks = new LogTicks('log');
        $this->name = 'log';
    }

    //----------------
    // PUBLIC METHODS

    // Translate between world and screen
    public function Translate($a)
    {
        if (!is_numeric($a)) {
            if ($a != '' && $a != '-' && $a != 'x') {
                Util\JpGraphError::RaiseL(11001);
                // ('Your data contains non-numeric values.');
            }
            return 1;
        }
        if ($a < 0) {
            Util\JpGraphError::RaiseL(11002);
            //("Negative data values can not be used in a log scale.");
            exit(1);
        }
        if ($a == 0) {
            $a = 1;
        }

        $a = log10($a);
        return ceil($this->off + ($a * 1.0 - $this->scale[0]) * $this->scale_factor);
    }

    // Relative translate (don't include offset) usefull when we just want
    // to know the relative position (in pixels) on the axis
    public function RelTranslate($a)
    {
        if (!is_numeric($a)) {
            if ($a != '' && $a != '-' && $a != 'x') {
                Util\JpGraphError::RaiseL(11001);
                //('Your data contains non-numeric values.');
            }
            return 1;
        }
        if ($a == 0) {
            $a = 1;
        }
        $a = log10($a);
        return round(($a * 1.0 - $this->scale[0]) * $this->scale_factor);
    }

    // Use bcpow() for increased precision
    public function GetMinVal()
    {
        if (function_exists("bcpow")) {
            return round(bcpow(10, $this->scale[0], 15), 14);
        } else {
            return round(pow(10, $this->scale[0]), 14);
        }
    }

    public function GetMaxVal()
    {
        if (function_exists("bcpow")) {
            return round(bcpow(10, $this->scale[1], 15), 14);
        } else {
            return round(pow(10, $this->scale[1]), 14);
        }
    }

    // Logarithmic autoscaling is much simplier since we just
    // set the min and max to logs of the min and max values.
    // Note that for log autoscale the "maxstep" the fourth argument
    // isn't used. This is just included to give the method the same
    // signature as the linear counterpart.
    public function AutoScale($img, $min, $max, $maxsteps, $majend = true)
    {
        if ($min == 0) {
            $min = 1;
        }

        if ($max <= 0) {
            Util\JpGraphError::RaiseL(11004);
            //('Scale error for logarithmic scale. You have a problem with your data values. The max value must be greater than 0. It is mathematically impossible to have 0 in a logarithmic scale.');
        }
        if (is_numeric($this->autoscale_min)) {
            $smin = round($this->autoscale_min);
            $smax = ceil(log10($max));
            if ($min >= $max) {
                Util\JpGraphError::RaiseL(25071); //('You have specified a min value with SetAutoMin() which is larger than the maximum value used for the scale. This is not possible.');
            }
        } else {
            $smin = floor(log10($min));
            if (is_numeric($this->autoscale_max)) {
                $smax = round($this->autoscale_max);
                if ($smin >= $smax) {
                    Util\JpGraphError::RaiseL(25072); //('You have specified a max value with SetAutoMax() which is smaller than the miminum value used for the scale. This is not possible.');
                }
            } else {
                $smax = ceil(log10($max));
            }

        }

        $this->Update($img, $smin, $smax);
    }

    //---------------
    // PRIVATE METHODS
} // Class
