<?php

namespace LibreNMS\IPMI;

/**
 * Represents an interface to query Intel Node Manager data.
 */
final class NodeManager
{
    // Ref: https://www.intel.com/content/dam/www/public/us/en/documents/technical-specifications/intel-power-node-manager-v3-spec.pdf
    private static $IPMI_RAW_CMD = [
        'get_nm_version' => 'raw 0x2e 0xca 0x57 0x01 0x00',
        'get_device_id' => 'raw 0x06 0x01',
        'init_sensor_agent' => 'raw 0x0a 0x2c 0x01',
        'init_sensor_agent_status' => 'raw 0x0a 0x2c 0x00',
    ];

    private $client;
    private $slaveChannelPrefix;

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
        $slaveAndChannel = $this->resolveSlaveAndChannel();
        // Available in 2.0
        $result['memory'] = NodeManager::parsePowerReadings($this->sendToManagementEngine('nm statistics power domain memory'));
        // Available in 2.0
        $result['cpu'] = NodeManager::parsePowerReadings($this->sendToManagementEngine('nm statistics power domain cpu'));
        // Available in 1.5
        $result['platform'] = NodeManager::parsePowerReadings($this->sendToManagementEngine('nm statistics power domain platform'));
        return $result;
    }

    /**
     * Gets a list of available power reading sensors.
     */
    public function getAvailablePowerReadings(): array
    {
        $readings = $this->getPowerReadings();
        $result = [];
        // TODO: detect based on NM version
        if ($readings['memory'] != 0) {
            array_push($result, ['memory', 'Intel ME Memory']);
        }

        if ($readings['cpu'] != 0) {
            array_push($result, ['cpu', 'Intel ME CPU']);
        }

        if ($readings['platform'] != 0) {
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

    private function resolveSlaveAndChannel(): string
    {
        // TODO: determine whether slave address is constant for all platforms.
        // TODO: determine channel.
        // $dump = $this->client->sendCommand('sdr dump');
        return '-c 0 -t 0x2c';
    }

    private function sendToManagementEngine(string $command)
    {
        return $this->client->sendCommand("$this->slaveChannelPrefix $command");
    }

    private function sendRawCommand(string $key, bool $useAdmin = false)
    {
        $result = $this->client->sendCommand(NodeManager::$IPMI_RAW_CMD[$key], $useAdmin);
        return explode(" ", $result);
    }
}
