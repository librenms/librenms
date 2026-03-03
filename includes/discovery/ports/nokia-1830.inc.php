<?php

/**
 * Nokia 1830 PSS/PSD Ports Discovery
 *
 * PSD devices have ifName but may not have ifDescr populated.
 * This ensures ifDescr is set from ifName for proper port discovery.
 *
 * @link       https://www.librenms.org
 */

// Ensure all ports have ifDescr set (use ifName if missing)
foreach ($port_stats as $ifIndex => $port) {
    if (empty($port_stats[$ifIndex]['ifDescr']) && ! empty($port_stats[$ifIndex]['ifName'])) {
        $port_stats[$ifIndex]['ifDescr'] = $port_stats[$ifIndex]['ifName'];
    }
}
