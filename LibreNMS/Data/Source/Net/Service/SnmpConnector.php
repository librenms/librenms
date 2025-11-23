<?php
/**
 * SnmpConnector.php
 *
 * -Description-
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Net\Service;

use Illuminate\Support\Facades\Log;

class SnmpConnector extends BaseConnector
{
    private string $community;
    private string $version;
    private ?string $securityName = null;
    private ?string $securityLevel = null;
    private ?string $authProtocol = null;
    private ?string $authPassphrase = null;
    private ?string $privProtocol = null;
    private ?string $privPassphrase = null;
    private readonly int $requestId;

    /**
     * @param string $ip
     * @param int $port
     * @param string $version SNMP version: '1', '2c', or '3'
     * @param string $community Community string for v1/v2c
     * @param string $oid OID to query (default: system.sysDescr.0)
     * @param array $v3Config SNMPv3 configuration array with keys:
     *                        - securityName
     *                        - securityLevel: 'noAuthNoPriv', 'authNoPriv', or 'authPriv'
     *                        - authProtocol: 'MD5' or 'SHA'
     *                        - authPassphrase
     *                        - privProtocol: 'DES' or 'AES'
     *                        - privPassphrase
     */
    public function __construct(
        string $ip,
        int $port = 161,
        string $version = '2c',
        string $community = 'public',
        private readonly string $oid = '1.3.6.1.2.1.1.1.0',
        array $v3Config = []
    ) {
        $device = \DeviceCache::getPrimary();
        if ($device->exists) {
            $port = $device->port;
        }

        parent::__construct($ip, $port);
        $this->requestId = mt_rand(1, 65535);

        if ($device->exists) {
            $this->version = substr((string) $device->snmpver, 1);
            $this->community = $device->community;

            if ($this->version === '3') {
                $this->securityName = $device->authname;
                $this->securityLevel = $device->authlevel;
                $this->authProtocol = $device->authalgo;
                $this->authPassphrase = $device->authpass;
                $this->privProtocol = $device->cryptoalgo;
                $this->privPassphrase = $device->cryptopass;
            }

        } else {
            $this->version = $version;
            $this->community = $community;

            if ($version === '3') {
                $this->securityName = $v3Config['securityName'] ?? '';
                $this->securityLevel = $v3Config['securityLevel'] ?? 'noAuthNoPriv';
                $this->authProtocol = $v3Config['authProtocol'] ?? null;
                $this->authPassphrase = $v3Config['authPassphrase'] ?? null;
                $this->privProtocol = $v3Config['privProtocol'] ?? null;
                $this->privPassphrase = $v3Config['privPassphrase'] ?? null;
            }
        }
    }

    public function connect(): bool
    {
        $this->createSocket(SOCK_DGRAM, SOL_UDP);

        $snmpPacket = $this->buildSnmpPacket();

        $bytesSent = socket_sendto($this->socket, $snmpPacket, strlen($snmpPacket), 0, $this->ip, $this->port);
        $this->waitForRead();

        if ($bytesSent === false) {
            throw new \RuntimeException("Failed to send SNMP packet to $this " . socket_strerror(socket_last_error()));
        }

        return true;
    }

    public function isServiceAvailable(): bool
    {
        $response = '';
        $from = '';
        $portFrom = 0;

        $bytesReceived = socket_recvfrom($this->socket, $response, 4096, 0, $from, $portFrom);

        if ($bytesReceived > 0 && $from === $this->ip) {
            // Basic validation: check if response is valid SNMP
            if ($this->isValidSnmpResponse($response)) {
                Log::info("Received valid SNMP response from $this->ip");
                return true;
            }
        }

        throw new \RuntimeException("Failed to verify peer for $this " . socket_strerror(socket_last_error($this->socket)));
    }

    /**
     * Build SNMP packet based on version
     */
    private function buildSnmpPacket(): string
    {
        return match ($this->version) {
            '1' => $this->buildSnmpV1Packet(),
            '2c' => $this->buildSnmpV2cPacket(),
            '3' => $this->buildSnmpV3Packet(),
            default => throw new \InvalidArgumentException("Unsupported SNMP version: {$this->version}"),
        };
    }

    /**
     * Build SNMPv1 GET request packet
     */
    private function buildSnmpV1Packet(): string
    {
        $version = $this->encodeInteger(0); // SNMPv1 = 0
        $community = $this->encodeOctetString($this->community);
        $pdu = $this->buildGetRequestPdu();

        $message = $version . $community . $pdu;
        return $this->encodeSequence($message);
    }

    /**
     * Build SNMPv2c GET request packet
     */
    private function buildSnmpV2cPacket(): string
    {
        $version = $this->encodeInteger(1); // SNMPv2c = 1
        $community = $this->encodeOctetString($this->community);
        $pdu = $this->buildGetRequestPdu();

        $message = $version . $community . $pdu;
        return $this->encodeSequence($message);
    }

    /**
     * Build SNMPv3 GET request packet (simplified, basic implementation)
     */
    private function buildSnmpV3Packet(): string
    {
        $version = $this->encodeInteger(3); // SNMPv3 = 3

        // Global header
        $msgId = $this->encodeInteger($this->requestId);
        $msgMaxSize = $this->encodeInteger(65507);
        $msgFlags = $this->encodeOctetString($this->getMsgFlags());
        $msgSecurityModel = $this->encodeInteger(3); // USM

        $globalData = $msgId . $msgMaxSize . $msgFlags . $msgSecurityModel;
        $headerData = $this->encodeSequence($globalData);

        // Security parameters (simplified for noAuthNoPriv)
        $engineId = $this->encodeOctetString('');
        $engineBoots = $this->encodeInteger(0);
        $engineTime = $this->encodeInteger(0);
        $userName = $this->encodeOctetString($this->securityName);
        $authParams = $this->encodeOctetString('');
        $privParams = $this->encodeOctetString('');

        $secParams = $engineId . $engineBoots . $engineTime . $userName . $authParams . $privParams;
        $msgSecurityParameters = $this->encodeOctetString($this->encodeSequence($secParams));

        // Scoped PDU
        $contextEngineId = $this->encodeOctetString('');
        $contextName = $this->encodeOctetString('');
        $pdu = $this->buildGetRequestPdu();

        $scopedPdu = $this->encodeSequence($contextEngineId . $contextName . $pdu);

        $message = $version . $headerData . $msgSecurityParameters . $scopedPdu;
        return $this->encodeSequence($message);
    }

    /**
     * Get message flags for SNMPv3
     */
    private function getMsgFlags(): string
    {
        $flags = 0x00;

        if ($this->securityLevel === 'authNoPriv') {
            $flags = 0x01; // auth, no priv
        } elseif ($this->securityLevel === 'authPriv') {
            $flags = 0x03; // auth and priv
        }

        $flags |= 0x04; // reportable

        return chr($flags);
    }

    /**
     * Build GET request PDU
     */
    private function buildGetRequestPdu(): string
    {
        $requestId = $this->encodeInteger($this->requestId);
        $errorStatus = $this->encodeInteger(0);
        $errorIndex = $this->encodeInteger(0);

        // Variable bindings
        $oid = $this->encodeOid($this->oid);
        $null = $this->encodeNull();
        $varbind = $this->encodeSequence($oid . $null);
        $varbindList = $this->encodeSequence($varbind);

        $pduContent = $requestId . $errorStatus . $errorIndex . $varbindList;

        // PDU type: GetRequest (0xa0)
        return chr(0xa0) . $this->encodeLength(strlen($pduContent)) . $pduContent;
    }

    /**
     * Encode ASN.1 SEQUENCE
     */
    private function encodeSequence(string $data): string
    {
        return chr(0x30) . $this->encodeLength(strlen($data)) . $data;
    }

    /**
     * Encode ASN.1 INTEGER
     */
    private function encodeInteger(int $value): string
    {
        $bytes = '';
        $tempValue = $value;

        if ($value == 0) {
            $bytes = chr(0);
        } else {
            while ($tempValue > 0) {
                $bytes = chr($tempValue & 0xFF) . $bytes;
                $tempValue >>= 8;
            }

            // Add padding byte if high bit is set
            if (ord($bytes[0]) & 0x80) {
                $bytes = chr(0) . $bytes;
            }
        }

        return chr(0x02) . $this->encodeLength(strlen($bytes)) . $bytes;
    }

    /**
     * Encode ASN.1 OCTET STRING
     */
    private function encodeOctetString(string $str): string
    {
        return chr(0x04) . $this->encodeLength(strlen($str)) . $str;
    }

    /**
     * Encode ASN.1 NULL
     */
    private function encodeNull(): string
    {
        return chr(0x05) . chr(0x00);
    }

    /**
     * Encode ASN.1 OBJECT IDENTIFIER
     */
    private function encodeOid(string $oid): string
    {
        $parts = explode('.', $oid);
        $encoded = '';

        // First two parts are combined: 40*first + second
        $encoded .= chr(40 * intval($parts[0]) + intval($parts[1]));

        for ($i = 2; $i < count($parts); $i++) {
            $value = intval($parts[$i]);
            $encoded .= $this->encodeOidSubidentifier($value);
        }

        return chr(0x06) . $this->encodeLength(strlen($encoded)) . $encoded;
    }

    /**
     * Encode OID subidentifier
     */
    private function encodeOidSubidentifier(int $value): string
    {
        if ($value < 128) {
            return chr($value);
        }

        $bytes = '';
        while ($value > 0) {
            $bytes = chr(($value & 0x7F) | ($bytes ? 0x80 : 0x00)) . $bytes;
            $value >>= 7;
        }

        return $bytes;
    }

    /**
     * Encode ASN.1 length
     */
    private function encodeLength(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $bytes = '';
        $tempLength = $length;
        while ($tempLength > 0) {
            $bytes = chr($tempLength & 0xFF) . $bytes;
            $tempLength >>= 8;
        }

        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    /**
     * Validate SNMP response
     */
    private function isValidSnmpResponse(string $response): bool
    {
        if (strlen($response) < 2) {
            return false;
        }

        // Check for SEQUENCE tag (0x30)
        if (ord($response[0]) !== 0x30) {
            return false;
        }

        // Basic validation passed
        return true;
    }
}
