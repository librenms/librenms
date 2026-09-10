<?php

/*
 * Nokia Wavence reports IF-MIB::ifName and IF-MIB::ifAlias as the literal
 * string "NULL". Clear those values and allow port_fill_missing_and_trim()
 * to populate them from ifDescr.
 */

foreach ($port_stats as &$wavence_port) {
    if (($wavence_port['ifName'] ?? '') === 'NULL') {
        $wavence_port['ifName'] = '';
    }

    if (($wavence_port['ifAlias'] ?? '') === 'NULL') {
        $wavence_port['ifAlias'] = '';
    }
}

unset($wavence_port);
