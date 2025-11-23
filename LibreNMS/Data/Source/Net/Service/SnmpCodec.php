<?php
/**
 * SnmpCodec.php
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

class SnmpCodec implements UdpCodec
{
    private readonly int $requestId;

    public function __construct(
        private readonly string $version = 'v2c',
        private readonly string $community = 'public',
        private readonly string $username = '',
        private readonly string $oid = '.1.3.6.1.2.1.1.2.0',
    ) {
        $this->requestId = mt_rand(1, 65535);
    }

    /**
     * Set engine parameters (typically after discovery)
     */
    public function setEngineParameters(string $engineId, int $engineBoots, int $engineTime): void
    {
        $this->engineId = $engineId;
        $this->engineBoots = $engineBoots;
        $this->engineTime = $engineTime;
        $this->engineDiscovered = true;
    }

    public function getPayload(): string
    {
        return match ($this->version) {
            'v1' => $this->buildSnmpV1Packet(),
            'v2c' => $this->buildSnmpV2cPacket(),
            'v3' => $this->buildSnmpV3DiscoveryPacket(),
            default => throw new \InvalidArgumentException("Unsupported SNMP version: {$this->version}"),
        };
    }

    public function validateResponse(string $payload): bool
    {
        if (strlen($payload) < 10) {
            return false;
        }

        // Check if it starts with SEQUENCE tag (0x30)
        if (ord($payload[0]) !== 0x30) {
            return false;
        }

        // Check for Get-Response PDU type (0xA2) or Report PDU (0xA8 for v3)
        return str_contains($payload, chr(0xA2)) || str_contains($payload, chr(0xA8));
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
     * Build SNMPv3 engine discovery packet
     * This works for service availability checking regardless of security configuration
     */
    private function buildSnmpV3DiscoveryPacket(): string
    {
        $version = $this->encodeInteger(3); // SNMPv3 = 3

        // Global header for discovery
        $msgId = $this->encodeInteger($this->requestId);
        $msgMaxSize = $this->encodeInteger(65507);
        $msgFlags = $this->encodeOctetString(chr(0x04)); // reportable flag only
        $msgSecurityModel = $this->encodeInteger(3); // USM

        $globalData = $msgId . $msgMaxSize . $msgFlags . $msgSecurityModel;
        $headerData = $this->encodeSequence($globalData);

        // Empty security parameters for discovery (but include username if provided)
        $engineId = $this->encodeOctetString('');
        $engineBoots = $this->encodeInteger(0);
        $engineTime = $this->encodeInteger(0);
        $userName = $this->encodeOctetString($this->username);
        $authParams = $this->encodeOctetString('');
        $privParams = $this->encodeOctetString('');

        $secParams = $engineId . $engineBoots . $engineTime . $userName . $authParams . $privParams;
        $msgSecurityParameters = $this->encodeOctetString($this->encodeSequence($secParams));

        // Empty scoped PDU for discovery - RFC 3414 requires empty varBindList
        $contextEngineId = $this->encodeOctetString('');
        $contextName = $this->encodeOctetString('');
        $pdu = $this->buildEmptyGetRequestPdu();
        $scopedPdu = $this->encodeSequence($contextEngineId . $contextName . $pdu);

        $message = $version . $headerData . $msgSecurityParameters . $scopedPdu;
        return $this->encodeSequence($message);
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
     * Build empty GET request PDU for SNMPv3 discovery
     * RFC 3414 requires empty varBindList for discovery
     */
    private function buildEmptyGetRequestPdu(): string
    {
        $requestId = $this->encodeInteger($this->requestId);
        $errorStatus = $this->encodeInteger(0);
        $errorIndex = $this->encodeInteger(0);

        // Empty variable bindings list
        $varbindList = $this->encodeSequence('');

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
        $parts = explode('.', trim($oid, '.'));
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
}
