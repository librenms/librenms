<?php

/**
 * NodeManager.php
 *
 * Node Manager
 *
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Trym Lund Flogard
 * @author     Trym Lund Flogard <trym@flogard.no>
 */

namespace LibreNMS\IPMI;

/**
 * Represents an interface to query Intel Node Manager data.
 *
 * Intel Intelligent Power Node Manager is an IPMI OEM extension
 * which allows for power and thermal monitoring on supported platforms.
 * See spec. v1.5 sect. 1.3 for more information.
 */
final class NodeManager
{
    /*
     * Relevant documentation:
     * spec. v1.5: https://www.intel.com/content/dam/doc/technical-specification/intelligent-power-node-manager-1-5-specification.pdf
     * spec. v2.0: https://www.intel.com/content/dam/www/public/us/en/documents/technical-specifications/intelligent-power-node-manager-specification.pdf
     * spec. v3.0: https://www.intel.com/content/dam/www/public/us/en/documents/technical-specifications/intel-power-node-manager-v3-spec.pdf
     */
    private const INTEL_MANUFACTURER_ID = '570100'; // 000157h
    private const IPMI_NM_RAW_CMD = [
        'get_nm_version' => 'raw 0x2e 0xca 0x57 0x01 0x00',
        'platform_global_power' => 'raw 0x2e 0xc8 0x57 0x01 0x00 0x01 0x00 0x00',
        'cpu_global_power' => 'raw 0x2e 0xc8 0x57 0x01 0x00 0x01 0x01 0x00',
        'memory_global_power' => 'raw 0x2e 0xc8 0x57 0x01 0x00 0x01 0x02 0x00',
    ];

    private $client;
    private $slaveChannelPrefix = '';
    private $nmVersion = null;

    /**
     * Creates a new instance of the Intel Node Manager class.
     *
     * @param  IPMIClient  $client  The IPMI client for the host.
     * @param  float  $version  Intel Node Manager version.
     * @param  string  $slaveChannelPrefix  I2C connection channel for sensor readings.
     */
    public function __construct(IPMIClient $client, ?float $version = null, string $slaveChannelPrefix = null)
    {
        $this->client = $client;
        if (! isset($version) && ! isset($slaveChannelPrefix)) {
            $this->discoverNodeManager();
        } else {
            $this->nmVersion = $version;
            $this->slaveChannelPrefix = $slaveChannelPrefix;
        }
    }

    public function discoverAttributes(): array
    {
        $attributes = [];
        $attributes['version'] = $this->nmVersion;
        $attributes['slave_channel_prefix'] = $this->slaveChannelPrefix;

        return $attributes;
    }

    /**
     * Gets a value indicating whether Intel Node Manager is supported for this device.
     */
    public function isPlatformSupported(): bool
    {
        return $this->nmVersion != null;
    }

    /**
     * Gets a list of available power reading sensors.
     *
     * @return array A 2-dim array of available sensors. First index is the name, second index is description.
     */
    public function discoverSensors(): array
    {
        if ($this->nmVersion == null) {
            return [];
        }

        // TODO: cross check with Get Node Manager Capabilities command (0xc9)
        $result = [];
        if ($this->nmVersion >= 2.0) {
            array_push($result, ['memory', 'Intel ME Memory']);
            array_push($result, ['cpu', 'Intel ME CPU']);
        }
        if ($this->nmVersion >= 1.5) {
            array_push($result, ['platform', 'Intel ME Platform']);
        }

        return $result;
    }

    /**
     * Gets sensor readings for this device.
     *
     * @return array An array of power reading values with sensor descriptions as the key.
     */
    public function pollSeonsors(): array
    {
        if ($this->nmVersion == null) {
            return [];
        }

        $result = [];
        if ($this->nmVersion >= 2.0) {
            if ($power = NodeManager::decodePowerReadings($this->sendRawCommand('memory_global_power'))) {
                $result['Intel ME Memory'] = $power;
            }

            if ($cpu = NodeManager::decodePowerReadings($this->sendRawCommand('cpu_global_power'))) {
                $result['Intel ME CPU'] = $cpu;
            }
        }

        if ($this->nmVersion >= 1.5) {
            if ($platform = NodeManager::decodePowerReadings($this->sendRawCommand('platform_global_power'))) {
                $result['Intel ME Platform'] = $platform;
            }
        }

        return $result;
    }

    private function discoverNodeManager(): void
    {
        // See spec. v3 sect. 4.5 BMC requirements for Intel® NM Discovery
        $sdr = bin2hex($this->client->getRawSDR());
        if (! $sdr) {
            d_echo('SDR is empty!!');

            return;
        }

        $decoded = NodeManager::decodeNMSDRRecord($sdr);
        if (! $decoded['supported']) {
            return;
        }

        $this->slaveChannelPrefix = '-b 0x0' . $decoded['channel'] . ' -t 0x' . $decoded['slaveAddress'];
        $this->nmVersion = NodeManager::decodeVersion($this->sendRawCommand('get_nm_version'));
        d_echo("Node manager version: $this->nmVersion");
    }

    private static function decodePowerReadings(array $raw): ?int
    {
        if (sizeof($raw) < 20) {
            return null;
        }

        // Raw value is little endian
        $current = join('', array_reverse(array_slice($raw, 3, 2)));

        return hexdec($current);
    }

    /**
     * Decodes the Intel Node Manager SDR record
     * from a binary SDR dump.
     *
     * Slave channel is 7-bit I2C Slave Address of NM controller on channel.
     */
    private static function decodeNMSDRRecord($sdrHex): array
    {
        // See NM spec. v3 sect 4.5 table 4-13.
        $headerOffset = strpos($sdrHex, NodeManager::INTEL_MANUFACTURER_ID);
        if (! $headerOffset) {
            d_echo("Intel Node Manager not supported.\n");

            return [
                'supported' => false,
            ];
        }

        $header = substr($sdrHex, $headerOffset, 22); // 11 byte header
        $slaveAddress = substr($header, 10, 2); // byte #5
        $channel = substr($header, 12, 1); // byte #6 first nibble
        d_echo("Intel Node Manager supported.\nSlave address: $slaveAddress\nChannel: $channel");

        return [
            'channel' => $channel,
            'slaveAddress' => $slaveAddress,
            'supported' => true,
        ];
    }

    private static function decodeVersion(array $raw): ?float
    {
        if (sizeof($raw) < 8) {
            return null;
        }

        // version is 5th byte (ipmitool trims response)
        switch ($raw[3]) {
            case '00':
                return null;
            case '01':
                return 1.0;
            case '02':
                return 1.5;
            case '03':
                return 2.0;
            case '04':
                return 2.5;
            case '05':
            default:
                return 3.0;
        }
    }

    private function sendRawCommand(string $key, bool $useAdmin = false): array
    {
        $result = $this->client->sendCommand($this->slaveChannelPrefix . ' ' . NodeManager::IPMI_NM_RAW_CMD[$key], $useAdmin);

        return explode(' ', trim($result));
    }
}
