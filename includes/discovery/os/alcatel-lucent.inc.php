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
    elseif (strpos($sysObjectId, '.1.3.6.1.4.1.6486.801') !== false) {
        $os = 'aos';
    }
    elseif (
        strpos($sysObjectId, '.1.3.6.1.4.1.6527.1.3') !== false
        || strpos($sysObjectId, '.1.3.6.1.4.1.6527.6.2.1.2.2.') !== false  // TiMOS-B-2.0.R3 both/mpc ALCATEL SAS-M 7210
        || strpos($sysObjectId, '.1.3.6.1.4.1.6527.1.6.1') !== false       // TiMOS-B-6.1.R14 both/hops ALCATEL ESS 7450
        || strpos($sysObjectId, '.1.3.6.1.4.1.6527.6.1.1.2.') !== false    // TiMOS-B-6.0.R2 both/hops ALCATEL-LUCENT SAR 7705
        || strpos($sysObjectId, '.1.3.6.1.4.1.6527.1.9.1') !== false       // TiMOS-B-6.1.R14 both/hops ALCATEL SR 7710
        || strpos($sysObjectId, '.1.3.6.1.4.1.6527.1.15.') !== false       // TiMOS-C-12.0.R16 cpm/hops64 ALCATEL XRS 7950
    ) {
        $os = 'timos';
    }
}//end if
