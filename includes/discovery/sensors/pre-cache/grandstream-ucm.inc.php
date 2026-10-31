<?php

use LibreNMS\OS\GrandstreamUcm;

// YAML cannot normalize arbitrary OCTET STRING values before it validates a state.
$ucmTrunkResponse = SnmpQuery::numericIndex()->options(['-OteQUSab', '-Pu', '-Ih'])->walk(GrandstreamUcm::TRUNK_TABLE);
$ucmTrunkFailed = ! $ucmTrunkResponse->isValid()
    && ($ucmTrunkResponse->exitCode !== 0 || $ucmTrunkResponse->stderr !== '');
$pre_cache[GrandstreamUcm::TRUNK_TABLE] = GrandstreamUcm::normalizeTrunks(
    $ucmTrunkResponse->valuesByIndex(),
    $ucmTrunkFailed ? $os->getDevice()->sensors : [],
);
unset($ucmTrunkResponse, $ucmTrunkFailed);
