<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'hh3cTransceiverInfoTable ';
$pre_cache['comware_oids'] = snmpwalk_cache_multi_oid($device, 'hh3cTransceiverInfoTable', [], 'HH3C-TRANSCEIVER-INFO-MIB');
