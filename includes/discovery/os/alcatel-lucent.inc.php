<?php

// | Alcatel-Lucent OS6850-U24X 6.4.3.520.R01 GA, April 08, 2010. | .1.3.6.1.4.1.6486.800.1.1.2.1.7.1.10 |
if (!$os) {
    if (strpos($sysObjectId, '.1.3.6.1.4.1.6486.800') !== false) {
        if (strstr($sysDescr, 'AOS-W')) {
            $os = 'arubaos';
        }
        else {
            $os = 'aos';
        }
    }

    if (strpos($sysObjectId, '.1.3.6.1.4.1.6486.801') !== false) {
        $os = 'aos';
    }

    if (strpos($sysObjectId, '.1.3.6.1.4.1.6527.1.3') !== false) {
        $os = 'timos';
    }

    // FIXME: make these less specific.
    // TiMOS-B-6.1.R14 both/hops ALCATEL ESS 7450
    if (strpos($sysObjectId, '.1.3.6.1.4.1.6527.1.6.1') !== false) {
        $os = 'timos';
    }

    // TiMOS-B-2.0.R3 both/mpc ALCATEL SAS-M 7210
    if (strpos($sysObjectId, '.1.3.6.1.4.1.6527.6.2.1.2.2.1') !== false) {
        $os = 'timos';
    }

    // TiMOS-B-6.1.R14 both/hops ALCATEL SR 7710
    if (strpos($sysObjectId, '.1.3.6.1.4.1.6527.1.9.1') !== false) {
        $os = 'timos';
    }
    // TiMOS-B-6.0.R2 both/hops ALCATEL-LUCENT SAR 7705
    if (strpos($sysObjectId, '.1.3.6.1.4.1.6527.6.1.1.2.1') !== false) {
        $os = 'timos';
    }    
    // TiMOS-B-6.1.R7 both/hops ALCATEL-LUCENT SAR 7705
    if (strpos($sysObjectId, '.1.3.6.1.4.1.6527.6.1.1.2.2') !== false) {
        $os = 'timos';
    }    
    // TiMOS-B-7.0.R5 both/hops ALCATEL SAR 7705
    if (strpos($sysObjectId, '.1.3.6.1.4.1.6527.6.1.1.2.5') !== false) {
        $os = 'timos';
    }    
    // TiMOS-B-6.0.R2 both/hops ALCATEL-LUCENT SAR 7705
    if (strpos($sysObjectId, '.1.3.6.1.4.1.6527.6.1.1.2.6') !== false) {
        $os = 'timos';
    }    
}//end if
