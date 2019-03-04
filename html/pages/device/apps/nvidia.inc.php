<?php
/*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @package    LibreNMS
* @subpackage webui
* @link       http://librenms.org
* @copyright  2019 LibreNMS
* @author     LibreNMS Contributors
*/

global $config;

$graphs = [
    'nvidia_sm' => 'GPU Utilization',
    'nvidia_mem' => 'Memory Utilization',
    'nvidia_enc' => 'Encoder Utilization',
    'nvidia_dec' => 'Decoder Utilization',
    'nvidia_fb' => 'Frame Buffer Memory Usage',
    'nvidia_bar1' => 'Bar1 Memory Usage',
    'nvidia_rxpci' => 'PCIe RX',
    'nvidia_txpci' => 'PCIe TX',
    'nvidia_pwr' => 'Power Usage',
    'nvidia_temp' => 'Temperature',
    'nvidia_mclk' => 'Memory Clock',
    'nvidia_pclk' => 'GPU Clock',
    'nvidia_pviol' => 'Thermal Violation Percentage',
    'nvidia_tviol' => 'Thermal Violation Boolean',
    'nvidia_sbecc' => 'Single Bit ECC Errors',
    'nvidia_dbecc' => 'Double Bit ECC Errors',
];

include "app.bootstrap.inc.php";
