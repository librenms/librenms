<?php

/** @var \LibreNMS\OS\HarmonicCableos $os */
// Supply the named columns YAML expects without requiring the vendor MIB.
$pre_cache['HARMONIC-DOCS-EXT-MIB::hrmCmtsRpdTable'] = $os->rpdStateDiscoveryData();
