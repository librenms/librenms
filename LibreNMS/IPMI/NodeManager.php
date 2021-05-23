<?php

namespace LibreNMS\IPMI;

use Exception;

/**
 * Represents an interface to query Intel Node Manager data.
 * 
 * Intel Intelligent Power Node Manager is an IPMI OEM extension
 * which allows for power and thermal monitoring on supported platforms.
 * See spec. v1.5 sect. 1.3 for more information.
 */
final class NodeManager
{
    /**
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

    private IPMIClient $client;
    private string $slaveChannelPrefix = '';
    private ?float $nmVersion = null;

    /**
     * Creates a new instance of the Intel Node Manager class.
     * @param IPMIClient $client The IPMI client for the host.
     */
    public function __construct(IPMIClient $client)
    {
        $this->client = $client;
    }

    /**
     * Gets a value indicating whether Intel Node Manager is supported for this device.
     */
    public function isPlatformSupported(): bool
    {
        $this->discoverNodeManager();
        return $this->nmVersion != null;
    }

    /**
     * Gets power readings for this device.
     */
    public function getPowerReadings()
    {
        $this->discoverNodeManager();
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

    /**
     * Gets a list of available power reading sensors.
     */
    public function getAvailablePowerSensors(): array
    {
        $this->discoverNodeManager();
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

    private function discoverNodeManager()
    {
        if ($this->nmVersion != null) {
            return;
        }

        // See spec. v3 sect. 4.5 BMC requirements for Intel® NM Discovery
        $sdr = bin2hex($this->client->getSDR());
        if (!$sdr) {
            d_echo('SDR is empty!!');
            return;
        }

        $decoded = NodeManager::decodeNMSDRRecord($sdr);
        if (!$decoded['supported']) {
            return;
        }

        $this->slaveChannelPrefix = "-b 0x0" . $decoded['channel'] . " -t 0x" . $decoded['slaveAddress'];
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
    private static function decodeNMSDRRecord($sdrHex)
    {
        // See NM spec. v3 sect 4.5 table 4-13.
        $headerOffset = strpos($sdrHex, NodeManager::INTEL_MANUFACTURER_ID);
        if (!$headerOffset) {
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

    private static function decodeVersion(array $raw): ?string
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

    private function sendRawCommand(string $key, bool $useAdmin = false)
    {
        $result = $this->client->sendCommand($this->slaveChannelPrefix . " " . NodeManager::IPMI_NM_RAW_CMD[$key], $useAdmin);
        return explode(' ', trim($result));
    }
}
