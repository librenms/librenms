<?php

use LibreNMS\RRD\RrdDefinition;

$oids = snmp_get_multi($device, ['pfStateTableCount.0', 'pfStateTableSearches.0', 'pfStateTableInserts.0', 'pfStateTableRemovals.0', 'pfCounterMatch.0', 'pfCounterBadOffset.0', 'pfCounterFragment.0', 'pfCounterShort.0', 'pfCounterNormalize.0', 'pfCounterMemDrop.0'], '-OQUs', 'BEGEMOT-PF-MIB');

$states = $oids[0]['pfStateTableCount'];
$searches = $oids[0]['pfStateTableSearches'];
$inserts = $oids[0]['pfStateTableInserts'];
$removals = $oids[0]['pfStateTableCount'];
$matches = $oids[0]['pfCounterMatch'];
$badoffset = $oids[0]['pfCounterBadOffset'];
$fragmented = $oids[0]['pfCounterFragment'];
$short = $oids[0]['pfCounterShort'];
$normalized = $oids[0]['pfCounterNormalize'];
$memdropped = $oids[0]['pfCounterMemDrop'];


if (is_numeric($states)) {
    $rrd_def = RrdDefinition::make()->addDataset('states', 'GAUGE', 0);

    $fields = array(
        'states' => $states,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_states', $tags, $fields);

    $os->enableGraph('pf_states');
}

if (is_numeric($searches)) {
    $rrd_def = RrdDefinition::make()->addDataset('searches', 'COUNTER', 0);

    $fields = array(
        'searches' => $searches,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_searches', $tags, $fields);

    $os->enableGraph('pf_searches');
}

if (is_numeric($inserts)) {
    $rrd_def = RrdDefinition::make()->addDataset('inserts', 'COUNTER', 0);

    $fields = array(
        'inserts' => $inserts,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_inserts', $tags, $fields);

    $os->enableGraph('pf_inserts');
}

if (is_numeric($removals)) {
    $rrd_def = RrdDefinition::make()->addDataset('removals', 'COUNTER', 0);

    $fields = array(
        'removals' => $removals,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_removals', $tags, $fields);

    $os->enableGraph('pf_removals');
}

if (is_numeric($matches)) {
    $rrd_def = RrdDefinition::make()->addDataset('matches', 'COUNTER', 0);

    $fields = array(
        'matches' => $matches,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_matches', $tags, $fields);

    $os->enableGraph('pf_matches');
}

if (is_numeric($badoffset)) {
    $rrd_def = RrdDefinition::make()->addDataset('badoffset', 'COUNTER', 0);

    $fields = array(
        'badoffset' => $badoffset,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_badoffset', $tags, $fields);

    $os->enableGraph('pf_badoffset');
}

if (is_numeric($fragmented)) {
    $rrd_def = RrdDefinition::make()->addDataset('fragmented', 'COUNTER', 0);

    $fields = array(
        'fragmented' => $fragmented,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_fragmented', $tags, $fields);

    $os->enableGraph('pf_fragmented');
}

if (is_numeric($short)) {
    $rrd_def = RrdDefinition::make()->addDataset('short', 'COUNTER', 0);

    $fields = array(
        'short' => $short,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_short', $tags, $fields);

    $os->enableGraph('pf_short');
}

if (is_numeric($normalized)) {
    $rrd_def = RrdDefinition::make()->addDataset('normalized', 'COUNTER', 0);

    $fields = array(
        'normalized' => $normalized,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_normalized', $tags, $fields);

    $os->enableGraph('pf_normalized');
}

if (is_numeric($memdropped)) {
    $rrd_def = RrdDefinition::make()->addDataset('memdropped', 'COUNTER', 0);

    $fields = array(
        'memdropped' => $memdropped,
    );

    $tags = compact('rrd_def');
    data_update($device, 'pf_memdropped', $tags, $fields);

    $os->enableGraph('pf_memdropped');
}
