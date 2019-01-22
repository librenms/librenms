<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Sonicwall extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        if (starts_with($this->getDevice()['sysObjectID'], '.1.3.6.1.4.1.8741.1')) {
            return array(
                Processor::discover(
                    'sonicwall',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.8741.1.3.1.3.0',  // SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicCurrentCPUUtil.0
                    0,
                    'CPU',
                    1
                )
            );
        } else {
            return array(
                Processor::discover(
                    'sonicwall',
                    $this->getDeviceId(),
                    $this->getDevice()['sysObjectID'] . '.2.1.3.0',  // different OID for each model
                    0,
                    'CPU',
                    1
                )
            );
        }
    }
}
