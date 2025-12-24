<?php

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Mempool;
use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Alaxala extends OS implements MempoolsDiscovery, ProcessorDiscovery
{
    protected function findMib(): ?string
    {
        $table = [
            '.1.3.6.1.4.1.21839.1.1.2' => 'AX7800R',  // ax7800r AXR-MIB
            '.1.3.6.1.4.1.21839.1.1.4' => 'AX2000R',  // ax2000r AX2000R-MIB
            '.1.3.6.1.4.1.21839.1.1.5' => 'AX7800R',  // ax7700r AXR-MIB
            '.1.3.6.1.4.1.21839.1.2.2' => 'AX7800S',  // ax7800s AXS-MIB
            '.1.3.6.1.4.1.21839.1.2.3' => 'AX7800S',  // ax5400s AXS-MIB
            '.1.3.6.1.4.1.21839.1.2.6' => 'AX2430S',  // ax2430s AX2430S-MIB
            '.1.3.6.1.4.1.21839.1.2.7' => 'AX3630S',  // ax3630s AX36S-MIB
            '.1.3.6.1.4.1.21839.1.2.8' => 'AX6300S',  // ax6300s AX63S-MIB
            '.1.3.6.1.4.1.21839.1.2.9' => 'AX6300S',  // ax6700s AX63S-MIB
            '.1.3.6.1.4.1.21839.1.2.10' => 'AX1230S', // ax1230s AX1230S-MIB
            '.1.3.6.1.4.1.21839.1.2.11' => 'AX3630S', // ax3640s AX36S-MIB
            '.1.3.6.1.4.1.21839.1.2.12' => 'AX6300S', // ax6600s AX63S-MIB
            '.1.3.6.1.4.1.21839.1.2.13' => 'AX1240S', // ax1240s AX12S-MIB
            '.1.3.6.1.4.1.21839.1.2.14' => 'AX1240S', // ax1250s AX12S-MIB
            '.1.3.6.1.4.1.21839.1.2.15' => 'AX3630S', // ax3650s AX36S-MIB
            '.1.3.6.1.4.1.21839.1.2.16' => 'AX2530S', // ax2530s AX2530S-MIB
            '.1.3.6.1.4.1.21839.1.2.17' => 'AX3630S', // ax3830s AX36S-MIB
            '.1.3.6.1.4.1.21839.1.2.18' => 'AX1240S', // ax2230s AX12S-MIB
            '.1.3.6.1.4.1.21839.1.2.20' => 'AX4630S', // ax4630s AX4630S-MIB
            '.1.3.6.1.4.1.21839.1.2.23' => 'AX260A',  // ax260a  AX260A-MIB
            '.1.3.6.1.4.1.21839.1.2.24' => 'AX3660S', // ax3660s AX3660S-MIB
            '.1.3.6.1.4.1.21839.1.2.25' => 'AX1240S', // ax2130s AX12S-MIB
        ];

        foreach ($table as $prefix => $mib) {
            if (Str::startsWith($this->getDevice()->sysObjectID, $prefix)) {
                return $mib;
            }
        }

        return null;
    }

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml baseline

        $mib = $this->findMib();
        if (! $mib) {
            return;
        }

        $profiles = [
            // axRouter Series
            '.1.3.6.1.4.1.21839.1.1.2' => [ // ax7800r AXR-MIB
                'hardware' => ['ax7800rChassisType.0', 'ax7800rChassisType.1'],
                'rom' => ['ax7800rBcuRmRomVersion.0', 'ax7800rBcuRmRomVersion.1'],
                'version' => ['ax7800rSoftwareVersion.0'],
                'serial' => ['ax7800rBcuSerialNumber.1', 'ax7800rNifSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.1.4' => [ // ax2000r AX2000R-MIB
                'hardware' => ['ax2000rChassisType.0', 'ax2000rChassisType.1'],
                'rom' => ['ax2000rRmRomVersion.0', 'ax2000rRmRomVersion.1'],
                'version' => ['ax2000rSoftwareVersion.0'],
                'serial' => ['ax2000rSerialNumber.0'],
            ],
            '.1.3.6.1.4.1.21839.1.1.5' => [ // ax7700r AXR-MIB
                'hardware' => ['ax7700rChassisType.0', 'ax7700rChassisType.1'],
                'rom' => ['ax7700rBcuRmRomVersion.0', 'ax7700rBcuRmRomVersion.1'],
                'version' => ['ax7700rSoftwareVersion.0'],
                'serial' => ['ax7700rBcuSerialNumber.1', 'ax7700rNifSerialNumber.1'],
            ],
            # axSwitch Series
            '.1.3.6.1.4.1.21839.1.2.2' => [ // ax7800s AXS-MIB
                'hardware' => ['ax7800sChassisType.0', 'ax7800sChassisType.1'],
                'rom' => ['ax7800sBcuRmRomVersion.0', 'ax7800sBcuRmRomVersion.1'],
                'version' => ['ax7800sSoftwareVersion.0'],
                'serial' => ['ax7800sBcuSerialNumber.1', 'ax7800sChassisSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.3' => [ // ax5400s AXS-MIB
                'hardware' => ['ax5400sChassisType.0', 'ax5400sChassisType.1'],
                'rom' => ['ax5400sBcuRmRomVersion.0', 'ax5400sBcuRmRomVersion.1'],
                'version' => ['ax5400sSoftwareVersion.0'],
                'serial' => ['ax5400sChassisSerialNumber.1', 'ax5400sBcuSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.6' => [ // ax2430s AX2430S-MIB
                'hardware' => ['ax2430sChassisType.0', 'ax2430sChassisType.1'],
                'rom' => ['ax2430sRomVersion.0', 'ax2430sRomVersion.1'],
                'version' => ['ax2430sSoftwareVersion.0'],
                'serial' => ['ax2430sChassisSerialNumber.0', 'ax2430sChassisSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.7' => [ // ax3630s AX36S-MIB
                'hardware' => ['ax3630sChassisType.0', 'ax3630sChassisType.1'],
                'rom' => ['ax3630sRomVersion.0', 'ax3630sRomVersion.1'],
                'version' => ['ax3630sSoftwareVersion.0'],
                'serial' => [], // use ENTITY-MIB fallback
            ],
            '.1.3.6.1.4.1.21839.1.2.8' => [ // ax6300s AX63S-MIB
                'hardware' => ['ax6300sChassisType.0', 'ax6300sChassisType.1'],
                'rom' => ['ax6300sMsuRomVersion.0', 'ax6300sMsuRomVersion.1'],
                'version' => ['ax6300sSoftwareVersion.0'],
                'serial' => ['ax6300sMsuSerialNumber.0', 'ax6300sMsuSerialNumber.1', 'ax6300sChassisSerialNumber.0', 'ax6300sChassisSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.9' => [ // ax6700s AX63S-MIB
                'hardware' => ['ax6700sChassisType.0', 'ax6700sChassisType.1'],
                'rom' => ['ax6700sBcuRomVersion.0', 'ax6700sBcuRomVersion.1'],
                'version' => ['ax6700sSoftwareVersion.0'],
                'serial' => ['ax6700sBcuSerialNumber.0', 'ax6700sBcuSerialNumber.1', 'ax6700sChassisSerialNumber.0', 'ax6700sChassisSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.10' => [ // ax1230s AX1230S-MIB
                'hardware' => ['ax1230sChassisType.0', 'ax1230sChassisType.1'],
                'rom' => ['ax1230sRomVersion.0', 'ax1230sRomVersion.1'],
                'version' => ['ax1230sSoftwareVersion.0'],
                'serial' => ['ax1230sChassisSerialNumber.0', 'ax1230sChassisSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.11' => [ // ax3640s   AX36S-MIB
                'hardware' => ['ax3640sChassisType.0', 'ax3640sChassisType.1'],
                'rom' => ['ax3640sRomVersion.0', 'ax3640sRomVersion.1'],
                'version' => ['ax3640sSoftwareVersion.0'],
                'serial' => [], // use ENTITY-MIB fallback
            ],
            '.1.3.6.1.4.1.21839.1.2.12' => [ // ax6600s AX63S-MIB
                'hardware' => ['ax6600sChassisType.0', 'ax6600sChassisType.1'],
                'rom' => ['ax6600sCsuRomVersion.0', 'ax6600sCsuRomVersion.1'],
                'version' => ['ax6600sSoftwareVersion.0'],
                'serial' => ['ax6600sCsuSerialNumber.0', 'ax6600sCsuSerialNumber.1', 'ax6600sChassisSerialNumber.0', 'ax6600sChassisSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.13' => [ // ax1240s AX12S-MIB
                'hardware' => ['ax1240sChassisType.0', 'ax1240sChassisType.1'],
                'rom' => ['ax1240sRomVersion.0', 'ax1240sRomVersion.1'],
                'version' => ['ax1240sSoftwareVersion.0'],
                'serial' => ['ax1240sChassisSerialNumber.0', 'ax1240sChassisSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.14' => [ // ax1250s AX12S-MIB
                'hardware' => ['ax1250sChassisType.0', 'ax1250sChassisType.1'],
                'rom' => ['ax1250sRomVersion.0', 'ax1250sRomVersion.1'],
                'version' => ['ax1250sSoftwareVersion.0'],
                'serial' => ['ax1250sChassisSerialNumber.0', 'ax1250sChassisSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.15' => [ // ax3650s AX36S-MIB
                'hardware' => ['ax3650sChassisType.0', 'ax3650sChassisType.1'],
                'rom' => ['ax3650sRomVersion.0', 'ax3650sRomVersion.1'],
                'version' => ['ax3650sSoftwareVersion.0'],
                'serial' => [], // use ENTITY-MIB fallback
            ],
            '.1.3.6.1.4.1.21839.1.2.16' => [ // ax2530s AX2530S-MIB
                'hardware' => ['ax2530sChassisType.0', 'ax2530sChassisType.1'],
                'rom' => ['ax2530sRomVersion.0', 'ax2530sRomVersion.1'],
                'version' => ['ax2530sSoftwareVersion.0'],
                'serial' => [],
            ],
            '.1.3.6.1.4.1.21839.1.2.17' => [ // ax3830s AX36S-MIB
                'hardware' => ['ax3830sChassisType.0', 'ax3830sChassisType.1'],
                'rom' => ['ax3830sRomVersion.0', 'ax3830sRomVersion.1'],
                'version' => ['ax3830sSoftwareVersion.0'],
                'serial' => ['ax3830sChassisSerialNumber.0', 'ax3830sChassisSerialNumber.1'], // fallback to ENTITY if empty
            ],
            '.1.3.6.1.4.1.21839.1.2.18' => [ // ax2230s AX12S-MIB
                'hardware' => ['ax2230sChassisType.0', 'ax2230sChassisType.1'],
                'rom' => ['ax2230sRomVersion.0', 'ax2230sRomVersion.1'],
                'version' => ['ax2230sSoftwareVersion.0'],
                'serial' => [],
            ],
            '.1.3.6.1.4.1.21839.1.2.20' => [ // ax4630s AX4630S-MIB
                'hardware' => ['ax4630sChassisType.0', 'ax4630sChassisType.1'],
                'rom' => ['ax4630sRomVersion.0', 'ax4630sRomVersion.1'],
                'version' => ['ax4630sSoftwareVersion.0'],
                'serial' => ['ax4630sNifSerialNumber.0', 'ax4630sNifSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.23' => [ // ax260a AX260A-MIB
                'hardware' => ['ax260aChassisType.0', 'ax260aChassisType.1'],
                'rom' => ['ax260aRomVersion.0', 'ax260aRomVersion.1'],
                'version' => ['ax260aSoftwareVersion.0'],
                'serial' => ['ax260aChassisSerialNumber.0', 'ax260aChassisSerialNumber.1'],
            ],
            '.1.3.6.1.4.1.21839.1.2.24' => [ // ax3660s AX3660S-MIB
                'hardware' => ['ax3660sChassisType.0', 'ax3660sChassisType.1'],
                'rom' => ['ax3660sRomVersion.0', 'ax3660sRomVersion.1'],
                'version' => ['ax3660sSoftwareVersion.0'],
                'serial' => ['ax3660sSerialNumber.0'],
            ],
            '.1.3.6.1.4.1.21839.1.2.25' => [ // ax2130s AX12S-MIB
                'hardware' => ['ax2130sChassisType.0', 'ax2130sChassisType.1'],
                'rom' => ['ax2130sRomVersion.0', 'ax2130sRomVersion.1'],
                'version' => ['ax2130sSoftwareVersion.0'],
                'serial' => [],
            ],
        ];

        foreach ($profiles as $prefix => $oids) {
            if (! Str::startsWith($device->sysObjectID, $prefix)) {
                continue;
            }

            if (empty($device->hardware)) {
                $device->hardware = $this->firstSnmpValue($device, $oids['hardware'], $mib, true);
            }

            if (empty($device->version)) {
                $rom = $this->firstSnmpValue($device, $oids['rom'], $mib) ?? '';
                $sw = $this->firstSnmpValue($device, $oids['version'], $mib) ?? '';
                $device->version = trim("$rom SW $sw") ?: null;
            }

            if (empty($device->serial)) {
                $device->serial = $this->firstSnmpValue($device, $oids['serial'], $mib)
                    ?: snmp_get($this->getDeviceArray(), 'ENTITY-MIB::entPhysicalSerialNum.1', '-OQv', null, 'alaxala')
                    ?: null;
            }

            $device->hardware = $this->normalizeHardware($device->hardware, $device->sysDescr);

            return;
        }
    }

    public function discoverProcessors()
    {
        $device = $this->getDevice();
        $mib = $this->findMib();
        if (! $mib) {
            return [];
        }

        $profiles = [
            '.1.3.6.1.4.1.21839.1.1.2' => [ // ax7800r AXR-MIB
                'cpu' => [
                    'ax7800rBcuRmCpuLoad1m.1',
                    'ax7800rBcuRmCpuLoad1m.0',
                    'ax7800rBcuCpCpuLoad1m.1',
                    'ax7800rBcuCpCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.1.4' => [ // ax2000r AX2000R-MIB
                'cpu' => [
                    'ax2000rRmCpuLoad1m.1',
                    'ax2000rRmCpuLoad1m.0',
                    'ax2000rRpCpuLoad1m.1',
                    'ax2000rRpCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.1.5' => [ // ax7700r AXR-MIB
                'cpu' => [
                    'ax7700rBcuRmCpuLoad1m.1',
                    'ax7700rBcuRmCpuLoad1m.0',
                    'ax7700rBcuCpCpuLoad1m.1',
                    'ax7700rBcuCpCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.2' => [ // ax7800s AXS-MIB
                'cpu' => [
                    'ax7800sBcuRmCpuLoad1m.1',
                    'ax7800sBcuRmCpuLoad1m.0',
                    'ax7800sBcuCpCpuLoad1m.1',
                    'ax7800sBcuCpCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.3' => [ // ax5400s AXS-MIB
                'cpu' => [
                    'ax5400sBcuRmCpuLoad1m.1',
                    'ax5400sBcuRmCpuLoad1m.0',
                    'ax5400sBcuCpCpuLoad1m.1',
                    'ax5400sBcuCpCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.6' => [ // ax2430s AX2430S-MIB
                'cpu' => [
                    'ax2430sCpuLoad1m.1',
                    'ax2430sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.7' => [ // ax3630s AX36S-MIB
                'cpu' => [
                    'ax3630sCpuLoad1m.1',
                    'ax3630sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.8' => [ // ax6300s AX63S-MIB
                'cpu' => [
                    'ax6300sMsuCpuLoad1m.1',
                    'ax6300sMsuCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.9' => [ // ax6700s AX63S-MIB
                'cpu' => [
                    'ax6700sBcuCpuLoad1m.1',
                    'ax6700sBcuCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.10' => [ // ax1230s AX1230S-MIB
                'cpu' => [
                    'ax1230sCpuLoad1m.1',
                    'ax1230sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.11' => [ // ax3640s AX36S-MIB
                'cpu' => [
                    'ax3640sCpuLoad1m.1',
                    'ax3640sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.12' => [ // ax6600s AX63S-MIB
                'cpu' => [
                    'ax6600sCsuCpuLoad1m.1',
                    'ax6600sCsuCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.13' => [ // ax1240s AX12S-MIB
                'cpu' => [
                    'ax1240sCpuLoad1m.1',
                    'ax1240sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.14' => [ // ax1250s AX12S-MIB
                'cpu' => [
                    'ax1250sCpuLoad1m.1',
                    'ax1250sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.15' => [ // ax3650s AX36S-MIB
                'cpu' => [
                    'ax3650sCpuLoad1m.1',
                    'ax3650sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.16' => [ // ax2530s AX2530S-MIB
                'cpu' => [
                    'ax2530sCpuLoad1m.1',
                    'ax2530sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.17' => [ // ax3830s AX36S-MIB
                'cpu' => [
                    'ax3830sCpuLoad1m.1',
                    'ax3830sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.18' => [ // ax2230s AX12S-MIB
                'cpu' => [
                    'ax2230sCpuLoad1m.1',
                    'ax2230sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.20' => [ // ax4630s AX4630S-MIB
                'cpu' => [
                    'ax4630sCpuLoad1m.1',
                    'ax4630sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.23' => [ // ax260a AX260A-MIB
                'cpu' => [
                    'ax260aCpuLoad1m.1',
                    'ax260aCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.24' => [ // ax3660s AX3660S-MIB
                'cpu' => [
                    'ax3660sCpuLoad1m.1',
                    'ax3660sCpuLoad1m.0',
                ],
            ],
            '.1.3.6.1.4.1.21839.1.2.25' => [ // ax2130s AX12S-MIB
                'cpu' => [
                    'ax2130sCpuLoad1m.1',
                    'ax2130sCpuLoad1m.0',
                ],
            ],
        ];

        foreach ($profiles as $prefix => $profile) {
            if (! Str::startsWith($device->sysObjectID, $prefix)) {
                continue;
            }

            $cpu = $this->firstSnmpValueWithOid($device, $profile['cpu'], $mib);
            if (! $cpu) {
                return [];
            }

            $proc_oid = $this->numericOid($device, $mib, $cpu['oid']);
            if (! $proc_oid) {
                return [];
            }

            return [
                Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    $proc_oid,
                    0
                ),
            ];
        }

        return [];
    }

    public function discoverMempools()
    {
        $device = $this->getDevice();
        $mib = $this->findMib();
        if (! $mib) {
            return collect();
        }

        $profiles = [
            '.1.3.6.1.4.1.21839.1.1.2' => [ // ax7800r AXR-MIB
                'mempool' => [[
                    'total' => ['ax7800rBcuRmMemoryTotalSize.1', 'ax7800rBcuRmMemoryTotalSize.0'],
                    'used' => ['ax7800rBcuRmMemoryUsedSize.1', 'ax7800rBcuRmMemoryUsedSize.0'],
                    'free' => ['ax7800rBcuRmMemoryFreeSize.1', 'ax7800rBcuRmMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.1.4' => [ // ax2000r AX2000R-MIB
                'mempool' => [
                    [
                        'total' => ['ax2000rRmMemoryTotalSize.1', 'ax2000rRmMemoryTotalSize.0'],
                        'used' => ['ax2000rRmMemoryUsedSize.1', 'ax2000rRmMemoryUsedSize.0'],
                        'free' => ['ax2000rRmMemoryFreeSize.1', 'ax2000rRmMemoryFreeSize.0'],
                    ],
                    [
                        'total' => ['ax2000rRpMemoryTotalSize.1', 'ax2000rRpMemoryTotalSize.0'],
                        'used' => ['ax2000rRpMemoryUsedSize.1', 'ax2000rRpMemoryUsedSize.0'],
                        'free' => ['ax2000rRpMemoryFreeSize.1', 'ax2000rRpMemoryFreeSize.0'],
                    ],
                ],
            ],
            '.1.3.6.1.4.1.21839.1.1.5' => [ // ax7700r AXR-MIB
                'mempool' => [[
                    'total' => ['ax7700rBcuRmMemoryTotalSize.1', 'ax7700rBcuRmMemoryTotalSize.0'],
                    'used' => ['ax7700rBcuRmMemoryUsedSize.1', 'ax7700rBcuRmMemoryUsedSize.0'],
                    'free' => ['ax7700rBcuRmMemoryFreeSize.1', 'ax7700rBcuRmMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.2' => [ // ax7800s AXS-MIB
                'mempool' => [[
                    'total' => ['ax7800sBcuRmMemoryTotalSize.1', 'ax7800sBcuRmMemoryTotalSize.0'],
                    'used' => ['ax7800sBcuRmMemoryUsedSize.1', 'ax7800sBcuRmMemoryUsedSize.0'],
                    'free' => ['ax7800sBcuRmMemoryFreeSize.1', 'ax7800sBcuRmMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.3' => [ // ax5400s AXS-MIB
                'mempool' => [[
                    'total' => ['ax5400sBcuRmMemoryTotalSize.1', 'ax5400sBcuRmMemoryTotalSize.0'],
                    'used' => ['ax5400sBcuRmMemoryUsedSize.1', 'ax5400sBcuRmMemoryUsedSize.0'],
                    'free' => ['ax5400sBcuRmMemoryFreeSize.1', 'ax5400sBcuRmMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.6' => [ // ax2430s AX2430S-MIB
                'mempool' => [[
                    'total' => ['ax2430sMemoryTotalSize.1', 'ax2430sMemoryTotalSize.0'],
                    'used' => ['ax2430sMemoryUsedSize.1', 'ax2430sMemoryUsedSize.0'],
                    'free' => ['ax2430sMemoryFreeSize.1', 'ax2430sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.7' => [ // ax3630s AX36S-MIB
                'mempool' => [[
                    'total' => ['ax3630sMemoryTotalSize.1', 'ax3630sMemoryTotalSize.0'],
                    'used' => ['ax3630sMemoryUsedSize.1', 'ax3630sMemoryUsedSize.0'],
                    'free' => ['ax3630sMemoryFreeSize.1', 'ax3630sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.8' => [ // ax6300s AX63S-MIB
                'mempool' => [[
                    'total' => ['ax6300sMsuMemoryTotalSize.1', 'ax6300sMsuMemoryTotalSize.0'],
                    'used' => ['ax6300sMsuMemoryUsedSize.1', 'ax6300sMsuMemoryUsedSize.0'],
                    'free' => ['ax6300sMsuMemoryFreeSize.1', 'ax6300sMsuMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.9' => [ // ax6700s AX63S-MIB
                'mempool' => [[
                    'total' => ['ax6700sBcuMemoryTotalSize.1', 'ax6700sBcuMemoryTotalSize.0'],
                    'used' => ['ax6700sBcuMemoryUsedSize.1', 'ax6700sBcuMemoryUsedSize.0'],
                    'free' => ['ax6700sBcuMemoryFreeSize.1', 'ax6700sBcuMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.10' => [ // ax1230s AX1230S-MIB
                'mempool' => [[
                    'total' => ['ax1230sMemoryTotalSize.1', 'ax1230sMemoryTotalSize.0'],
                    'used' => ['ax1230sMemoryUsedSize.1', 'ax1230sMemoryUsedSize.0'],
                    'free' => ['ax1230sMemoryFreeSize.1', 'ax1230sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.11' => [ // ax3640s AX36S-MIB
                'mempool' => [[
                    'total' => ['ax3640sMemoryTotalSize.1', 'ax3640sMemoryTotalSize.0'],
                    'used' => ['ax3640sMemoryUsedSize.1', 'ax3640sMemoryUsedSize.0'],
                    'free' => ['ax3640sMemoryFreeSize.1', 'ax3640sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.12' => [ // ax6600s AX63S-MIB
                'mempool' => [[
                    'total' => ['ax6600sCsuMemoryTotalSize.1', 'ax6600sCsuMemoryTotalSize.0'],
                    'used' => ['ax6600sCsuMemoryUsedSize.1', 'ax6600sCsuMemoryUsedSize.0'],
                    'free' => ['ax6600sCsuMemoryFreeSize.1', 'ax6600sCsuMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.13' => [ // ax1240s AX12S-MIB
                'mempool' => [[
                    'total' => ['ax1240sMemoryTotalSize.1', 'ax1240sMemoryTotalSize.0'],
                    'used' => ['ax1240sMemoryUsedSize.1', 'ax1240sMemoryUsedSize.0'],
                    'free' => ['ax1240sMemoryFreeSize.1', 'ax1240sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.14' => [ // ax1250s AX12S-MIB
                'mempool' => [[
                    'total' => ['ax1250sMemoryTotalSize.1', 'ax1250sMemoryTotalSize.0'],
                    'used' => ['ax1250sMemoryUsedSize.1', 'ax1250sMemoryUsedSize.0'],
                    'free' => ['ax1250sMemoryFreeSize.1', 'ax1250sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.15' => [ // ax3650s AX36S-MIB
                'mempool' => [[
                    'total' => ['ax3650sMemoryTotalSize.1', 'ax3650sMemoryTotalSize.0'],
                    'used' => ['ax3650sMemoryUsedSize.1', 'ax3650sMemoryUsedSize.0'],
                    'free' => ['ax3650sMemoryFreeSize.1', 'ax3650sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.16' => [ // ax2530s AX2530S-MIB
                'mempool' => [[
                    'total' => ['ax2530sMemoryTotalSize.1', 'ax2530sMemoryTotalSize.0'],
                    'used' => ['ax2530sMemoryUsedSize.1', 'ax2530sMemoryUsedSize.0'],
                    'free' => ['ax2530sMemoryFreeSize.1', 'ax2530sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.17' => [ // ax3830s AX36S-MIB
                'mempool' => [[
                    'total' => ['ax3830sMemoryTotalSize.1', 'ax3830sMemoryTotalSize.0'],
                    'used' => ['ax3830sMemoryUsedSize.1', 'ax3830sMemoryUsedSize.0'],
                    'free' => ['ax3830sMemoryFreeSize.1', 'ax3830sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.18' => [ // ax2230s AX12S-MIB
                'mempool' => [[
                    'total' => ['ax2230sMemoryTotalSize.1', 'ax2230sMemoryTotalSize.0'],
                    'used' => ['ax2230sMemoryUsedSize.1', 'ax2230sMemoryUsedSize.0'],
                    'free' => ['ax2230sMemoryFreeSize.1', 'ax2230sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.20' => [ // ax4630s AX4630S-MIB
                'mempool' => [[
                    'total' => ['ax4630sMemoryTotalSize.1', 'ax4630sMemoryTotalSize.0'],
                    'used' => ['ax4630sMemoryUsedSize.1', 'ax4630sMemoryUsedSize.0'],
                    'free' => ['ax4630sMemoryFreeSize.1', 'ax4630sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.23' => [ // ax260a AX260A-MIB
                'mempool' => [[
                    'total' => ['ax260aMemoryTotalSize.1', 'ax260aMemoryTotalSize.0'],
                    'used' => ['ax260aMemoryUsedSize.1', 'ax260aMemoryUsedSize.0'],
                    'free' => ['ax260aMemoryFreeSize.1', 'ax260aMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.24' => [ // ax3660s AX3660S-MIB
                'mempool' => [[
                    'total' => ['ax3660sMemoryTotalSize.1', 'ax3660sMemoryTotalSize.0'],
                    'used' => ['ax3660sMemoryUsedSize.1', 'ax3660sMemoryUsedSize.0'],
                    'free' => ['ax3660sMemoryFreeSize.1', 'ax3660sMemoryFreeSize.0'],
                ]],
            ],
            '.1.3.6.1.4.1.21839.1.2.25' => [ // ax2130s AX12S-MIB
                'mempool' => [[
                    'total' => ['ax2130sMemoryTotalSize.1', 'ax2130sMemoryTotalSize.0'],
                    'used' => ['ax2130sMemoryUsedSize.1', 'ax2130sMemoryUsedSize.0'],
                    'free' => ['ax2130sMemoryFreeSize.1', 'ax2130sMemoryFreeSize.0'],
                ]],
            ],
        ];

        foreach ($profiles as $prefix => $profile) {
            if (! Str::startsWith($device->sysObjectID, $prefix)) {
                continue;
            }

            foreach ($profile['mempool'] as $set) {
                $total = $this->firstSnmpValueWithOid($device, $set['total'], $mib);
                $used = $this->firstSnmpValueWithOid($device, $set['used'], $mib);
                $free = $this->firstSnmpValueWithOid($device, $set['free'], $mib);

                if (! $total || (! $used && ! $free)) {
                    continue;
                }

                $mempool = new Mempool([
                    'mempool_index' => 0,
                    'mempool_type' => 'Alaxala',
                    'mempool_class' => 'system',
                    'mempool_precision' => 1,
                    'mempool_descr' => 'Memory',
                    'mempool_perc_warn' => 90,
                ]);

                $total_oid = $this->numericOid($device, $mib, $total['oid']);
                if ($total_oid) {
                    $mempool->mempool_total_oid = $total_oid;
                }

                if ($used) {
                    $used_oid = $this->numericOid($device, $mib, $used['oid']);
                    if ($used_oid) {
                        $mempool->mempool_used_oid = $used_oid;
                    }
                } elseif ($free) {
                    $free_oid = $this->numericOid($device, $mib, $free['oid']);
                    if ($free_oid) {
                        $mempool->mempool_free_oid = $free_oid;
                    }
                }

                $mempool->fillUsage($used['value'] ?? null, $total['value'] ?? null, $free['value'] ?? null);

                return collect([$mempool]);
            }

            return collect();
        }

        return collect();
    }

    private function firstSnmpValue(Device $device, array $oids, string $mib, bool $enumStrings = false): ?string
    {
        $result = $this->firstSnmpValueWithOid($device, $oids, $mib, $enumStrings);

        return $result['value'] ?? null;
    }

    private function firstSnmpValueWithOid(Device $device, array $oids, string $mib, bool $enumStrings = false): ?array
    {
        foreach ($oids as $oid) {
            $query = SnmpQuery::device($device)
                ->mibDir('alaxala')
                ->mibs(['AX-SMI-MIB', $mib]);

            if ($enumStrings) {
                $query->enumStrings();
            }

            $response = $query->get($oid);

            if ($response->isValid()) {
                $val = $response->value();
                if ($val !== '') {
                    return [
                        'oid' => $oid,
                        'value' => $val,
                    ];
                }
            }
        }

        return null;
    }

    private function numericOid(Device $device, string $mib, string $oid): ?string
    {
        $oid = Str::contains($oid, '::') ? $oid : $mib . '::' . $oid;

        $numeric = SnmpQuery::device($device)
            ->mibDir('alaxala')
            ->mibs(['AX-SMI-MIB', $mib])
            ->numeric()
            ->translate($oid);

        return $numeric !== '' ? $numeric : null;
    }

    private function normalizeHardware(?string $hardware, ?string $sysDescr): ?string
    {
        $hardware = $hardware !== null ? trim($hardware) : null;
        if ($hardware === '') {
            $hardware = null;
        }

        if ($hardware !== null) {
            if (preg_match('/model-(?<model>[^()]+)/i', $hardware, $match)) {
                return trim($match['model']);
            }
            if (preg_match('/^(?<model>[^()]+)\\(\\d+\\)$/', $hardware, $match)) {
                return trim($match['model']);
            }
        }

        if ($sysDescr) {
            if (preg_match('/\\[(AX[^\\]]+)\\]/', $sysDescr, $match)) {
                return $match[1];
            }
            if (preg_match('/\\[(?<model>[^\\]]+)\\]/', $sysDescr, $match)) {
                return $match['model'];
            }
        }

        return $hardware;
    }
}
