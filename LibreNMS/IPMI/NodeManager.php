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

    private static $INTEL_MANUFACTURER_ID = '570100'; // 000157h
    private static $IPMI_NM_RAW_CMD = [
        'get_nm_version' => 'raw 0x2e 0xca 0x57 0x01 0x00',
    ];

    private IPMIClient $client;
    private string $slaveChannelPrefix = '';
    private ?float $nmVersion = null;

    public function __construct(IPMIClient $client)
    {
        $this->client = $client;
    }

    /**
     * Gets a value indicating whether Intel Node Manager is supported for this device.
     */
    public function isPlatformSupported(): bool
    {
        // TODO: determine whether the platform is supported based on get_device_id.
        return true;
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
            $result['memory'] = NodeManager::parsePowerReadings($this->slaveChannelPrefix . " " . $this->client->sendCommand('nm statistics power domain memory'));
            $result['cpu'] = NodeManager::parsePowerReadings($this->slaveChannelPrefix . " " . $this->client->sendCommand('nm statistics power domain cpu'));
        } elseif ($this->nmVersion >= 1.5) {
            $result['platform'] = NodeManager::parsePowerReadings($this->slaveChannelPrefix . " " . $this->client->sendCommand('nm statistics power domain platform'));
        }
        return $result;
    }

    /**
     * Gets a list of available power reading sensors.
     */
    public function getAvailablePowerReadings(): array
    {
        $this->discoverNodeManager();
        if ($this->nmVersion == null) {
            return [];
        } 

        $result = [];
        if ($this->nmVersion >= 2.0) {
            array_push($result, ['memory', 'Intel ME Memory']);
            array_push($result, ['cpu', 'Intel ME CPU']);
        } elseif ($this->nmVersion >= 1.5) {
            array_push($result, ['platform', 'Intel ME Platform']);
        }

        return $result;
    }

    private static function parsePowerReadings($value)
    {
        if (preg_match('/Instantaneous reading:\s+(?<watts>[0-9]+) Watts/', $value, $matches)) {
            return intval($matches['watts']);
        }

        return 0;
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
        $headerOffset = strpos($sdrHex, NodeManager::$INTEL_MANUFACTURER_ID);
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

    /**
     * Gets the domains that are supported by NM.
     */
    private static function enumerateSupportedDomains()
    {
        // Domain 0 (Platform) is supported in v1.5
        // 2.0 adds 
    }

    private static function decodeVersion(array $raw): ?string
    {
        d_echo($raw);
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

    private function discoverNodeManager()
    {
        // See spec. v3 sect. 4.5 BMC requirements for Intel® NM Discovery
        $sdr = bin2hex($this->client->getSDR());
        if (!$sdr) {
            d_echo('SDR is empty!!');
            return;
        }
        d_echo("SDR: $sdr\n\n");
        $decoded = NodeManager::decodeNMSDRRecord($sdr);
        $this->slaveChannelPrefix = "-b 0x0" . $decoded['channel'] . " -t 0x" . $decoded['slaveAddress'];
        $this->nmVersion = NodeManager::decodeVersion($this->sendRawCommand('get_nm_version'));
        d_echo("Node manager version: $this->nmVersion");
    }

    private function sendRawCommand(string $key, bool $useAdmin = false)
    {
        $result = $this->client->sendCommand($this->slaveChannelPrefix . " " . NodeManager::$IPMI_NM_RAW_CMD[$key], $useAdmin);
        return explode(' ', trim($result));
    }
}
