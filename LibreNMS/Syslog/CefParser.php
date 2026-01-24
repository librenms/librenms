<?php
/**
 * CefParser.php
 *
 * Parser for Common Event Format (CEF) syslog messages.
 * https://www.microfocus.com/documentation/arcsight/arcsight-smartconnectors-8.3/cef-implementation-standard/
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
 * @copyright  2026 LibreNMS
 * @author     LibreNMS Contributors <librenms-project@google.groups.com>
 */

namespace LibreNMS\Syslog;

class CefParser
{
    public int $version;
    public string $deviceVendor;
    public string $deviceProduct;
    public string $deviceVersion;
    public string $deviceEventClassId;
    public string $name;
    public string|int $severity;
    public array $extension = [];

    private ?string $syslogHost = null;

    public static function isCef(string $message): bool
    {
        return str_contains($message, 'CEF:');
    }

    public static function parse(string $message): ?self
    {
        // Extract hostname from syslog header (last word before CEF:)
        $syslogHost = null;
        if (preg_match('/(\S+)\s+CEF:/', $message, $hostMatch)) {
            $syslogHost = $hostMatch[1];
        }

        if (! preg_match('/CEF:(?<version>\d+)\|(?<header>.*)$/s', $message, $matches)) {
            return null;
        }

        $headerParts = self::splitHeader($matches['header']);
        if (count($headerParts) < 7) {
            return null;
        }

        $parser = new self();
        $parser->syslogHost = $syslogHost;
        $parser->version = (int) $matches['version'];
        $parser->deviceVendor = self::unescape($headerParts[0]);
        $parser->deviceProduct = self::unescape($headerParts[1]);
        $parser->deviceVersion = self::unescape($headerParts[2]);
        $parser->deviceEventClassId = self::unescape($headerParts[3]);
        $parser->name = self::unescape($headerParts[4]);
        $parser->severity = self::unescape($headerParts[5]);
        $parser->extension = self::parseExtension($headerParts[6] ?? '');

        return $parser;
    }

    private static function splitHeader(string $header): array
    {
        $parts = [];
        $current = '';
        $len = strlen($header);

        for ($i = 0; $i < $len; $i++) {
            if ($header[$i] === '\\' && $i + 1 < $len) {
                $current .= $header[$i] . $header[++$i];
            } elseif ($header[$i] === '|') {
                $parts[] = $current;
                $current = '';
                if (count($parts) === 6) {
                    $parts[] = substr($header, $i + 1);
                    break;
                }
            } else {
                $current .= $header[$i];
            }
        }

        if (count($parts) < 7) {
            $parts[] = $current;
        }

        return $parts;
    }

    private static function unescape(string $value): string
    {
        return str_replace(['\\|', '\\\\'], ['|', '\\'], $value);
    }

    private static function parseExtension(string $extension): array
    {
        $result = [];
        $extension = trim($extension);
        if (empty($extension)) {
            return $result;
        }

        preg_match_all('/(?:^|\s)([a-zA-Z0-9_]+)=/', $extension, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] as $i => $match) {
            $key = $match[0];
            $start = $match[1] + strlen($key) + 1;
            $end = isset($matches[0][$i + 1]) ? $matches[0][$i + 1][1] : strlen($extension);
            $value = rtrim(substr($extension, $start, $end - $start));
            $result[$key] = str_replace(['\\=', '\\\\', '\\n', '\\r', '\\"'], ['=', '\\', "\n", "\r", '"'], $value);
        }

        return $result;
    }

    public function getDeviceHostname(): ?string
    {
        if (! empty($this->syslogHost)) {
            return $this->syslogHost;
        }

        foreach (['dvchost', 'deviceHostName', 'shost', 'sourceHostName'] as $field) {
            if (! empty($this->extension[$field])) {
                return $this->extension[$field];
            }
        }

        return null;
    }

    public function getMessage(): ?string
    {
        return $this->extension['msg'] ?? $this->extension['message'] ?? null;
    }

    public function getProgram(): string
    {
        return trim($this->deviceVendor . ' ' . $this->deviceProduct);
    }
}
