<?php

/**
 * IPMIClient.php
 *
 * IPMI Client
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
 * @copyright  2021 Trym Lund Flogard
 * @author     Trym Lund Flogard <trym@flogard.no>
 */

namespace LibreNMS\IPMI;

class IPMIClient
{
    private static $INTEL_PREFIX = '5701000d01';
    private static $IPMI_RAW_CMD = [
        'get_nm_version' => 'raw 0x2e 0xca 0x57 0x01 0x00',
        'get_device_id' => 'raw 0x06 0x01',
        'init_sensor_agent' => 'raw 0x0a 0x2c 0x01',
        'init_sensor_agent_status' => 'raw 0x0a 0x2c 0x00',
    ];

    private $ipmiToolPath;
    private $host;
    private $user;
    private $password;
    private $port = NULL;

    private $privLvl = 'USER';
    private $interface = NULL;

    private $channel;
    private $slave;

    public function __construct($ipmiToolPath, $host, $user, $password)
    {
        $this->ipmiToolPath = $ipmiToolPath;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    public function getInterface($interface)
    {
        return $this->interface;
    }

    public function setInterface($interface)
    {
        $this->interface = $interface;
    }

    public function getPort()
    {
    }

    public function setPort($port)
    {
    }

    public function getSensors()
    {
        return explode(PHP_EOL, $this->send('sensor'));
    }

    public function getSensorDataRepository()
    {
        return explode(PHP_EOL, $this->send('-c sdr'));
    }

    public function getNodeManagerPowerStatistics()
    {
        return $this->send('nm statistics power', 0, 0x2c);
    }

    public function isNodeManagerSupported()
    {
        $this->initNodeManager();
        $v = $this->getNodeManagerVersion();
    }

    private static function packHex(int $value)
    {
        return "0x" + bin2hex(pack('c', $value));
    }

    private function send($command, int $channel = 0, int $transit_address = 0, $escalate = false)
    {
        $cmd = [$this->ipmiToolPath];
        if ($this->host != 'localhost') {
            array_push($cmd, '-H', $this->host, '-U', $this->user, '-P', $this->password);
            array_push($cmd, '-L', $escalate ? $this->privLvl : 'ADMINISTRATOR');
            if ($this->port) {
                array_push($cmd, '-p', $this->port);
            }
        }

        array_push($cmd, '-I', $this->interface, '-b', IPMIClient::packHex($channel), '-t', IPMIClient::packHex($transit_address));
        $cmd = array_merge($cmd, explode(" ", $command));

        return external_exec($cmd);
    }

    private function initNodeManager()
    {
        if ($this->initSensorAgentProcess()[0] == '01')
            return true;
        # Run sensor initialization agent
        for ($i = 0; $i < 3; $i++) {
            $this->initSensorAgent();
            sleep(1);
            if ($this->initSensorAgentProcess()[0] == '01')
                return true;
        }

        return false;
    }

    /**
     * Run initialization agent.
     */
    private function initSensorAgentProcess()
    {
        $result = $this->send(IPMIClient::$IPMI_RAW_CMD['init_sensor_agent_status']);
        return explode(" ", $result);
    }

    /**
     * Check the status of initialization agent.
     */
    private function initSensorAgent()
    {
        $result = $this->send(IPMIClient::$IPMI_RAW_CMD['init_sensor_agent']);
        return explode(" ", $result);
    }

    /**
     * Intel Node Manager capability checking
     *
     * This function is used to detect if compute node support Intel Node
     * Manager(return version number) or not(return -1) and parse out the
     * slave address and channel number of node manager.
     */
    private function getNodeManagerVersion()
    {
        $manufacturerId = $this->getDeviceId()['Manufacturer_ID'];
        // if MANUFACTURER_ID_INTEL != self.manufacturer_id:
        //     # If the manufacturer is not Intel, just set False and return.
        //     return 0

        $this->discoverSlaveChannel();
        $support = $this->getNMDeviceId()['Implemented_firmware'];
        # According to Intel Node Manager spec, return value of GET_DEVICE_ID,
        # bits 3 to 0 shows if Intel NM implemented or not.
        if (intval($support[0], 16) & 0xf == 0)
            return 0;

        return $this->getNMVersion()[4];
    }

    private function discoverSlaveChannel()
    {
        $dump = $this->send('sdr dump');
        $ret = $this->parseSlaveAndChannel($dump);
        $this->channel = join(['0x', $ret['slave']]);
        $this->slave = join(['0x', $ret['channel']]);
    }

    private function parseSlaveAndChannel($dump)
    {
        $prefix = IPMIClient::$INTEL_PREFIX;
        # According to Intel Node Manager spec, section 4.5, for Intel NM
        # discovery OEM SDR records are type C0h. It contains manufacture ID
        # and OEM data in the record body.
        # 0-2 bytes are OEM ID, byte 3 is 0Dh and byte 4 is 01h. Byte 5, 6
        # is Intel NM device slave address and channel number/sensor owner LUN.
        $data_str = bin2hex($dump);

        $data_str = hex2str($data_str);
        $oem_id_index = strpos($data_str, $prefix);
        if ($oem_id_index) {
            $offset = $oem_id_index + strlen($prefix);
            $ret = substr($data_str, $offset, $offset + 4);
            # Byte 5 is slave address. [7:4] from byte 6 is channel
            # number, so just pick ret[2] here.
            return ['slave' => substr($ret, 0, 2), 'channel' => $ret[2]];
        }
    }

    private function getDeviceId()
    {
        return $this->send(IPMIClient::$IPMI_RAW_CMD['get_device_id']);
    }

    private function getNMDeviceId()
    {
        return $this->send(IPMIClient::$IPMI_RAW_CMD['get_device_id'], $this->channel, $this->slave);
    }

    private function getNMVersion()
    {
        return $this->send(IPMIClient::$IPMI_RAW_CMD['get_nm_version'], $this->channel, $this->slave);
    }
}
