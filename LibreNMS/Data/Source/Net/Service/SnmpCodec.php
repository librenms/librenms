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
    private ?string $securityName = null;
    private ?string $securityLevel = null;
    private ?string $authProtocol = null;
    private ?string $authPassphrase = null;
    private ?string $privProtocol = null;
    private ?string $privPassphrase = null;
    private readonly int $requestId;

    // SNMPv3 engine discovery state
    private string $engineId = '';
    private int $engineBoots = 0;
    private int $engineTime = 0;
    private bool $engineDiscovered = false;

    public function __construct(
        private string $version = 'v2c',
        private string $community = 'public',
        array $v3Config = [],
        private readonly string $oid = '.1.3.6.1.2.1.1.2.0',
    ) {
        $this->requestId = mt_rand(1, 65535);

        if ($version === 'v3') {
            $this->securityName = $v3Config['authname'] ?? '';
            $this->securityLevel = $v3Config['authlevel'] ?? 'noAuthNoPriv';
            $this->authProtocol = $v3Config['authalgo'] ?? null;
            $this->authPassphrase = $v3Config['authpass'] ?? null;
            $this->privProtocol = $v3Config['cryptoalgo'] ?? null;
            $this->privPassphrase = $v3Config['cryptopass'] ?? null;

            // Validate security level configuration
            $this->validateV3Config();
        }
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
            'v3' => $this->buildSnmpV3Packet(),
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

        // Check for Get-Response PDU type (0xA2)
        return str_contains($payload, chr(0xA2));
    }

    /**
     * Validate SNMPv3 configuration
     */
    private function validateV3Config(): void
    {
        if ($this->securityLevel === 'authNoPriv' || $this->securityLevel === 'authPriv') {
            if (empty($this->authProtocol) || empty($this->authPassphrase)) {
                throw new \InvalidArgumentException('Authentication requires authProtocol and authPassphrase');
            }

            if (!in_array($this->authProtocol, ['MD5', 'SHA', 'SHA-224', 'SHA-256', 'SHA-384', 'SHA-512'])) {
                throw new \InvalidArgumentException('Invalid auth protocol: ' . $this->authProtocol);
            }
        }

        if ($this->securityLevel === 'authPriv') {
            if (empty($this->privProtocol) || empty($this->privPassphrase)) {
                throw new \InvalidArgumentException('Privacy requires privProtocol and privPassphrase');
            }

            if (!in_array($this->privProtocol, ['DES', 'AES', 'AES-128', 'AES-192', 'AES-256'])) {
                throw new \InvalidArgumentException('Invalid privacy protocol: ' . $this->privProtocol);
            }
        }
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
     * Build SNMPv3 GET request packet with full USM support
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

        // Build scoped PDU (before encryption)
        $contextEngineId = $this->encodeOctetString($this->engineId);
        $contextName = $this->encodeOctetString('');
        $pdu = $this->buildGetRequestPdu();
        $scopedPdu = $this->encodeSequence($contextEngineId . $contextName . $pdu);

        // Encrypt scoped PDU if privacy is enabled
        $privParams = '';
        if ($this->securityLevel === 'authPriv') {
            $privKey = $this->generatePrivacyKey();
            [$scopedPdu, $privParams] = $this->encryptScopedPdu($scopedPdu, $privKey);
        }

        // Build security parameters
        $engineId = $this->encodeOctetString($this->engineId);
        $engineBoots = $this->encodeInteger($this->engineBoots);
        $engineTime = $this->encodeInteger($this->engineTime);
        $userName = $this->encodeOctetString($this->securityName ?? '');

        // Placeholder for authentication parameters (12 bytes of zeros)
        $authParams = $this->encodeOctetString(str_repeat("\x00", 12));
        $privParamsEncoded = $this->encodeOctetString($privParams);

        $secParams = $engineId . $engineBoots . $engineTime . $userName . $authParams . $privParamsEncoded;
        $msgSecurityParameters = $this->encodeOctetString($this->encodeSequence($secParams));

        // Build complete message
        $message = $version . $headerData . $msgSecurityParameters . $scopedPdu;
        $wholeMsg = $this->encodeSequence($message);

        // Calculate and insert authentication parameters if needed
        if ($this->securityLevel === 'authNoPriv' || $this->securityLevel === 'authPriv') {
            $authKey = $this->generateAuthKey();
            $authParams = $this->calculateAuthParams($wholeMsg, $authKey);

            // Replace placeholder auth params with real ones
            $wholeMsg = $this->replaceAuthParams($wholeMsg, $authParams);
        }

        return $wholeMsg;
    }

    /**
     * Get message flags for SNMPv3
     */
    private function getMsgFlags(): string
    {
        $flags = 0x04; // reportable flag

        if ($this->securityLevel === 'authNoPriv') {
            $flags |= 0x01; // auth, no priv
        } elseif ($this->securityLevel === 'authPriv') {
            $flags |= 0x03; // auth and priv
        }

        return chr($flags);
    }

    /**
     * Generate localized authentication key using password-to-key algorithm
     */
    private function generateAuthKey(): string
    {
        $password = $this->authPassphrase;
        $algo = $this->getHashAlgorithm($this->authProtocol);

        // Password to key transformation (RFC 3414)
        $passwordHash = $this->passwordToKey($password, $algo);

        // Localize the key with engineID
        return $this->localizeKey($passwordHash, $this->engineId, $algo);
    }

    /**
     * Generate localized privacy key
     */
    private function generatePrivacyKey(): string
    {
        $password = $this->privPassphrase;
        $algo = $this->getHashAlgorithm($this->authProtocol); // Use auth algo for key derivation

        // Password to key transformation
        $passwordHash = $this->passwordToKey($password, $algo);

        // Localize the key with engineID
        return $this->localizeKey($passwordHash, $this->engineId, $algo);
    }

    /**
     * Password-to-key algorithm (RFC 3414)
     */
    private function passwordToKey(string $password, string $hashAlgo): string
    {
        $passwordLength = strlen($password);
        $count = 0;
        $buffer = '';

        // Generate 1MB of hashed data
        while ($count < 1048576) {
            for ($i = 0; $i < $passwordLength; $i++) {
                $buffer .= $password[$i];
                $count++;
                if ($count >= 1048576) {
                    break;
                }
            }
        }

        return hash($hashAlgo, $buffer, true);
    }

    /**
     * Localize key with engineID
     */
    private function localizeKey(string $passwordKey, string $engineId, string $hashAlgo): string
    {
        return hash($hashAlgo, $passwordKey . $engineId . $passwordKey, true);
    }

    /**
     * Calculate authentication parameters (HMAC)
     */
    private function calculateAuthParams(string $wholeMsg, string $authKey): string
    {
        $algo = $this->getHashAlgorithm($this->authProtocol);
        $hmac = hash_hmac($algo, $wholeMsg, $authKey, true);

        // Use first 12 bytes (96 bits) for MD5/SHA-1, first 16/24/32 bytes for SHA-2
        return substr($hmac, 0, 12);
    }

    /**
     * Replace authentication parameters placeholder with actual HMAC
     */
    private function replaceAuthParams(string $message, string $authParams): string
    {
        // Find the authentication parameters field (12 zero bytes) and replace it
        $placeholder = $this->encodeOctetString(str_repeat("\x00", 12));
        $replacement = $this->encodeOctetString($authParams);

        // Find position of placeholder in message
        $pos = strpos($message, $placeholder);
        if ($pos !== false) {
            return substr_replace($message, $replacement, $pos, strlen($placeholder));
        }

        return $message;
    }

    /**
     * Encrypt scoped PDU using privacy protocol
     */
    private function encryptScopedPdu(string $scopedPdu, string $privKey): array
    {
        $protocol = $this->privProtocol;

        if ($protocol === 'DES') {
            return $this->encryptDES($scopedPdu, $privKey);
        } elseif (str_starts_with($protocol, 'AES')) {
            return $this->encryptAES($scopedPdu, $privKey, $protocol);
        }

        throw new \RuntimeException('Unsupported privacy protocol: ' . $protocol);
    }

    /**
     * Encrypt using DES in CBC mode
     */
    private function encryptDES(string $data, string $privKey): array
    {
        // DES key is first 8 bytes of privacy key
        $desKey = substr($privKey, 0, 8);

        // Pre-IV is last 8 bytes of privacy key
        $preIV = substr($privKey, 8, 8);

        // Generate salt (8 bytes)
        $salt = random_bytes(8);

        // IV is XOR of pre-IV and salt
        $iv = $preIV ^ $salt;

        // Pad data to 8-byte boundary
        $paddedData = $this->padData($data, 8);

        // Encrypt
        $encrypted = openssl_encrypt($paddedData, 'des-cbc', $desKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        return [$encrypted, $salt];
    }

    /**
     * Encrypt using AES in CFB mode
     */
    private function encryptAES(string $data, string $privKey, string $protocol): array
    {
        // Determine key size
        $keySize = match($protocol) {
            'AES-192' => 24,
            'AES-256' => 32,
            default => 16,
        };

        $aesKey = substr($privKey, 0, $keySize);

        // Generate salt (8 bytes for AES-128, varies for others)
        $salt = random_bytes(8);

        // Build IV (engine boots + engine time + salt)
        $iv = pack('N', $this->engineBoots) . pack('N', $this->engineTime) . $salt;

        // Encrypt using AES-CFB
        $cipher = match($protocol) {
            'AES-192' => 'aes-192-cfb',
            'AES-256' => 'aes-256-cfb',
            default => 'aes-128-cfb',
        };

        $encrypted = openssl_encrypt($data, $cipher, $aesKey, OPENSSL_RAW_DATA, $iv);

        return [$encrypted, $salt];
    }

    /**
     * Pad data to block size boundary
     */
    private function padData(string $data, int $blockSize): string
    {
        $padLength = $blockSize - (strlen($data) % $blockSize);
        if ($padLength === $blockSize) {
            return $data;
        }
        return $data . str_repeat("\x00", $padLength);
    }

    /**
     * Get hash algorithm name for hash/hash_hmac functions
     */
    private function getHashAlgorithm(string $protocol): string
    {
        return match(strtoupper($protocol)) {
            'MD5' => 'md5',
            'SHA', 'SHA-1' => 'sha1',
            'SHA-224' => 'sha224',
            'SHA-256' => 'sha256',
            'SHA-384' => 'sha384',
            'SHA-512' => 'sha512',
            default => 'sha1',
        };
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

