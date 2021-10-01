<?php

/*
 * Copyright (C) 2015 Mark Schouten <mark@tuxis.nl>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2 dated June,
 * 1991.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * See https://www.gnu.org/licenses/gpl.txt for the full license
 */

require 'includes/html/graphs/common.inc.php';

$proxmox_rrd = Rrd::proxmoxName($vars['cluster'], $vars['vmid'], $vars['port']);

if (Rrd::checkRrdExists($proxmox_rrd)) {
    $rrd_filename = $proxmox_rrd;
}

$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

require 'includes/html/graphs/generic_data.inc.php';
