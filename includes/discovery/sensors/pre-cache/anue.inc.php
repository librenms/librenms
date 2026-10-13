<?php

$pre_cache['ANUE-MIB::anueNtoFilterDefTable'] ??= [];
\SnmpQuery::mibs(['ANUE-MIB'])
    ->bulk(true)
    ->numericIndex()
    ->options(['-OteQUSab', '-Pu', '-Ih'])
    ->walk('ANUE-MIB::anueNtoFilterDefTable')
    ->valuesByIndex($pre_cache['ANUE-MIB::anueNtoFilterDefTable']);
