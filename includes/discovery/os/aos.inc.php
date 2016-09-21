<?php

// | Alcatel-Lucent OS6850-U24X 6.4.3.520.R01 GA, April 08, 2010. | .1.3.6.1.4.1.6486.800.1.1.2.1.7.1.10 |
if (starts_with($sysObjectId, '.1.3.6.1.4.1.6486.800')) {
    if (!str_contains($sysDescr, 'AOS-W')) {
        $os = 'aos';
    }
} elseif (starts_with($sysObjectId, '.1.3.6.1.4.1.6486.801')) {
    $os = 'aos';
}
