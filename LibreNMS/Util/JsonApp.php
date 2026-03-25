<?php

namespace LibreNMS\Util;

use Illuminate\Support\Facades\Cache;
use LibreNMS\Exceptions\InvalidOidException;
use LibreNMS\Exceptions\JsonAppBlankJsonException;
use LibreNMS\Exceptions\JsonAppExtendErroredException;
use LibreNMS\Exceptions\JsonAppGzipDecodeException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\Exceptions\JsonAppPollingFailedException;
use LibreNMS\Exceptions\JsonAppWrongVersionException;
use SnmpQuery;

readonly class JsonApp
{
    public function __construct(
        public string $app,
        public string $version,
        public array $data,
        public int $error = 0,
        public string $errorString = '',
    ) {
    }

    /**
     * @throws JsonAppBlankJsonException
     * @throws JsonAppExtendErroredException
     * @throws JsonAppMissingKeysException
     * @throws JsonAppParsingFailedException
     * @throws JsonAppPollingFailedException
     * @throws JsonAppWrongVersionException
     * @throws JsonAppGzipDecodeException
     */
    public static function fetch(string $app, string $minVersion = '1.0'): self
    {
        $raw_output = Cache::driver('array')->get('agent_data')['app'][$app] ?? null;

        if ($raw_output === null) {
            try {
                $raw_output = SnmpQuery::walk("NET-SNMP-EXTEND-MIB::nsExtendOutputFull.\"$app\"")->value();
            } catch (InvalidOidException) {
                $raw_output = '';
            }
        }

        $output = self::parseBase64Encoded(trim($raw_output));

        if (empty($output)) {
            throw new JsonAppPollingFailedException('Empty return from device.', -2);
        }

        $json_data = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonAppParsingFailedException('Invalid JSON', $raw_output, -3);
        }

        if (empty($json_data)) {
            throw new JsonAppBlankJsonException('Blank JSON returned.', $output, -4);
        }

        if (! isset($json_data['error'], $json_data['data'], $json_data['errorString'], $json_data['version'])) {
            throw new JsonAppMissingKeysException('Legacy script or extend error, missing one or more required keys.', $output, $json_data, -5);
        }

        if (version_compare($json_data['version'], $minVersion, '<')) {
            throw new JsonAppWrongVersionException("Script,'" . $json_data['version'] . "', older than required version of '$minVersion'", $output, $json_data, -6);
        }

        if ($json_data['error'] != 0) {
            throw new JsonAppExtendErroredException("Script returned exception: {$json_data['errorString']}", $output, $json_data, $json_data['error']);
        }

        return new self(
            $app,
            $json_data['version'],
            $json_data['data'],
            $json_data['error'],
            $json_data['errorString'],
        );
    }

    private static function parseBase64Encoded(string $output): string
    {
        $decoded = base64_decode($output, true);

        if ($decoded === false) {
            return $output;
        }

        // Check gzip magic bytes (1F 8B)
        if (strlen($decoded) < 2 || ! str_starts_with($decoded, "\x1f\x8b")) {
            return $decoded;
        }

        $unzipped = gzdecode($decoded);

        if ($unzipped === false) {
            throw new JsonAppGzipDecodeException('Gzip decode failed.', $output, -8);
        }

        return $unzipped;
    }
}
