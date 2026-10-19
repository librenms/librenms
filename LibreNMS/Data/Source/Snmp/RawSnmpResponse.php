<?php

/**
 * RawSnmpResponse.php
 *
 * SnmpResponse subclass built from raw net-snmp command line output.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Snmp;

use App\Facades\LibrenmsConfig;
use LibreNMS\Util\StringHelpers;

class RawSnmpResponse extends SnmpResponse
{
    /**
     * Grammar of the left side of a varbind as net-snmp prints it.
     *
     * net-snmp always prints the full instance, so a varbind left side is an
     * object name followed by an index.  Every rendering LibreNMS can request
     * is covered, all verified against net-snmp 5.9.4 with the -O combinations
     * buildOutputFlags() can emit:
     *
     * - -On numeric OIDs:              .1.3.6.1.2.1.1.1.0
     * - the default / -Os / -OS:       IF-MIB::ifDescr.1
     * - symbolic sub-ids (MIB enums):  HOST-RESOURCES-TYPES::hrStorageRam.1
     * - quoted string sub-ids:         IF-MIB::ifName."my port"
     * - -OX bracket index groups:      IF-MIB::ifName["my port"]
     * - -Ou / -Of symbolic paths:      iso.org.dod.internet...ifDescr.1
     *                                  enterprises.vmware...vmwVmGuestOS.85877
     * - an unclosed bracket, emitted for a table whose index MIB could not be
     *   resolved:                     IF-MIB::ifName[1.2.3.4.
     *
     * Free text cannot match it, because everything a varbind ends with is an
     * index.  An ESXi guest dumping /etc/SuSE-release into vmwVmGuestOS makes
     * the value contain "VERSION = 42.3", "CODENAME = Malachite" and "# /etc/
     * SuSE-release is deprecated ..." lines, and none of them is a varbind.
     *
     * A bare object name is the only rendering without an index: net-snmp prints
     * it when an agent answers a scalar at the object itself, and it is only
     * accepted when it carries a MIB module, which free text does not.
     */
    private const VARBIND_LEFT_SIDE_REGEX = '/
        ^
        (?:
            # -On: every sub-identifier is numeric
            \.?\d+(?:\.\d+)+
          |
            # a bare object name, which net-snmp prints when an agent answers a
            # scalar at the object itself; only distinguishable from free text,
            # such as "VERSION = 42.3", by the MIB module it carries
            [A-Za-z_][\w-]*::[A-Za-z_][\w-]*
          |
            (?:[A-Za-z_][\w-]*::)?      # optional MIB module, i.e. IF-MIB::
            \.?                         # leading dot of the -Of root path
            [A-Za-z_][\w-]*             # object name
            (?:
                (?:
                    \.\d+               # numeric sub-id
                  | \."[^"]*"           # quoted string sub-id
                  | \[[^\n]*\]          # -OX index group, contents printed raw
                  | \.[A-Za-z_][\w-]*   # symbolic sub-id
                )*                      # any leading sub-ids
                (?:                     # the index net-snmp prints last
                    \.\d+
                  | \."[^"]*"
                  | \[[^\n]*\]
                  | \[[^\n]*$           # unclosed bracket, unresolvable index MIB
                )
                (?:\.\d+)*              # numeric parts after an -OX group
            )
        )
        $
    /x';

    protected readonly string $raw;
    private ?bool $inferValueEncoding = null;

    /**
     * Create a new raw response object filled with output from the net-snmp command.
     *
     * @param  array<int, string>  $command
     */
    public function __construct(
        string $output = '',
        string $stderr = '',
        int $exitCode = 0,
        array $command = [],
    ) {
        parent::__construct(
            rawValues: [],
            stderr: $stderr,
            exitCode: $exitCode,
            command: $command,
        );

        $this->raw = (string) preg_replace('/Wrong Type \(should be .*\): /', '', $output);
    }

    /**
     * An agent error phrase is only reported when it is the value of a varbind,
     * so a phrase inside a multi-line value is not mistaken for a failure.
     */
    protected function findBadString(): ?string
    {
        foreach ($this->rawLines() as $line) {
            $position = strpos($line, self::KEY_VALUE_DELIMITER);
            if ($position === false) {
                continue; // agent errors are always printed with their instance
            }

            if (! $this->isVarbindStart(substr($line, 0, $position))) {
                continue;
            }

            $value = substr($line, $position + strlen(self::KEY_VALUE_DELIMITER));

            if (preg_match(self::BAD_STRING_REGEX, $value, $matches) && ! str_starts_with($value, '"')) {
                // net-snmp never quotes an agent error, so a quoted value holding
                // one of these phrases is data and not a failure
                return $matches[0];
            }
        }

        return null;
    }

    public function isEmpty(): bool
    {
        return empty($this->raw);
    }

    /**
     * @return array<string, mixed>
     */
    public function values(): array
    {
        if (! empty($this->values)) {
            return $this->values;
        }

        $this->inferValueEncoding ??= ! StringHelpers::isValidUtf8($this->raw);
        $this->values = [];
        $lines = $this->rawLines();

        // First pass: a quoted value is read up to its closing quote, so nothing
        // inside it, not even a varbind shaped line, can cut it in half.
        $unclosedQuote = $this->readVarbinds($lines);

        if ($unclosedQuote !== null) {
            // The opening quote was part of a raw value, not of a quoted one.
            // Read the response again from there without quote tracking, so the
            // varbinds the unclosed quote swallowed are not dropped.
            $this->readVarbinds($lines, from: $unclosedQuote, trackQuotes: false);
        }

        return $this->values;
    }

    /**
     * Read varbinds out of $lines into the values, starting at $from.
     *
     * @param  string[]  $lines
     * @param  int  $from  index of the first line to read
     * @param  bool  $trackQuotes  read a quoted value up to its closing quote
     * @return ?int line holding a quoted value that never closed
     */
    private function readVarbinds(array $lines, int $from = 0, bool $trackQuotes = true): ?int
    {
        $oid = null;         // left side of the varbind being read
        $value = '';         // its value, which may span several lines
        $quoteAt = null;     // line the quoted value was opened on
        $scanned = 0;        // length of $value already scanned for the closing quote
        $escaped = false;    // backslash escape state carried between lines

        foreach ($lines as $i => $line) {
            if ($i < $from) {
                continue;
            }

            if ($quoteAt !== null) {
                // a quoted value is read whole: no line can interrupt it
                $value .= PHP_EOL . $line;
                $closed = $this->scanClosingQuote($value, $scanned, $escaped);
                if ($closed !== false) {
                    $value = substr($value, 0, $closed); // nothing prints after the quote
                    $quoteAt = null;
                }
                continue;
            }

            $parts = explode(self::KEY_VALUE_DELIMITER, $line, 2);
            $isVarbind = count($parts) === 2 && $this->isVarbindStart($parts[0]);

            // an error line cannot continue a value either: it is dropped
            if (preg_match(self::BAD_VALUE_REGEX, $line) && ! ($oid !== null && ! $isVarbind)) {
                continue;
            }

            if ($isVarbind) {
                $this->storeValue($oid, $value);

                [$oid, $value] = $parts;
                if ($trackQuotes && str_starts_with($value, '"')) {
                    $quoteAt = $i;
                    $scanned = 1; // the opening quote does not close the value
                    $escaped = false;
                    $closed = $this->scanClosingQuote($value, $scanned, $escaped);
                    if ($closed !== false) {
                        $value = substr($value, 0, $closed);
                        $quoteAt = null;
                    }
                }
                continue;
            }

            if ($oid !== null) {
                $value .= PHP_EOL . $line; // net-snmp wraps long values over lines
                continue;
            }

            // no delimiter at all (snmptranslate style output), keep it unindexed
            [$oid, $value] = ['', $line];
        }

        if ($quoteAt !== null) {
            // the quote never closed, so the varbind it opened was not stored
            return $quoteAt;
        }

        $this->storeValue($oid, $value);

        return null;
    }

    /**
     * Does this left side start a new varbind, or continue the value being read?
     */
    private function isVarbindStart(string $candidate): bool
    {
        return (bool) preg_match(self::VARBIND_LEFT_SIDE_REGEX, $candidate);
    }

    /**
     * Scan a quoted value for its closing quote, honouring the backslash escapes
     * net-snmp prints inside a quoted value.
     *
     * @return false|int offset just past the closing quote
     */
    private function scanClosingQuote(string $value, int &$scanned, bool &$escaped): int|false
    {
        for ($i = $scanned; $i < strlen($value); $i++) {
            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($value[$i] === '\\') {
                $escaped = true;
                continue;
            }

            if ($value[$i] === '"') {
                $escaped = false;
                $scanned = $i + 1;

                return $i + 1;
            }
        }

        $scanned = strlen($value);

        return false;
    }

    /**
     * The lines of the raw output.  net-snmp terminates its output with a newline,
     * which is not a line of data.  A carriage return inside a value is data, so
     * only the line feed separates lines.
     *
     * @return string[]
     */
    private function rawLines(): array
    {
        $lines = explode(PHP_EOL, $this->raw);

        while ($lines && end($lines) === '') {
            array_pop($lines);
        }

        return $lines;
    }

    /**
     * Store a finished varbind, applying the value clean up to every one of them.
     */
    private function storeValue(?string $oid, string $value): void
    {
        if ($oid === null) {
            return;
        }

        if (LibrenmsConfig::get('snmp.unescape')) {
            $value = stripslashes($value);
        }

        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = trim(stripslashes($value), "\" \n\r");
        } else {
            $value = trim($value);
        }

        $this->values[$oid] = $this->inferValueEncoding
            ? StringHelpers::inferEncoding($value)
            : $value;
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function append(SnmpResponse $response): SnmpResponse
    {
        if ($response instanceof RawSnmpResponse) {
            $newResponse = new RawSnmpResponse(
                $this->raw . $response->raw,
                $this->stderr . $response->stderr,
                $this->exitCode ?: $response->exitCode,
                $response->command ?: $this->command,
            );
        } else {
            $newResponse = new SnmpResponse(
                array_merge($this->values(), $response->values()),
                $this->stderr . $response->stderr,
                $this->exitCode ?: $response->exitCode,
                $response->command ?: $this->command,
            );
        }

        $newResponse->errorMessage = $this->errorMessage ?: $response->errorMessage;

        return $newResponse;
    }

    public function __sleep()
    {
        return ['raw', 'exitCode', 'stderr', 'command', 'values', 'errorMessage'];
    }
}
