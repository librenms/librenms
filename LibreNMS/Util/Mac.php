<?php
/**
 * Mac.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Mac
{
    private array $mac = [];

    public function __construct(string $mac)
    {
        $mac = strtolower(trim($mac));

        if (preg_match('/^([0-9a-f]{1,2})[-:.]?([0-9a-f]{1,2})[-:.]?([0-9a-f]{1,2})[-:.]?([0-9a-f]{1,2})[-:.]?([0-9a-f]{1,2})[-:.]?([0-9a-f]{1,2})$/', $mac, $matches)) {
            // strings without delimiters must have 12 characters
            if (! preg_match('/^[0-9a-f]{0,11}$/', $mac)) {
                $this->mac = [
                    str_pad($matches[1], 2, '0', STR_PAD_LEFT),
                    str_pad($matches[2], 2, '0', STR_PAD_LEFT),
                    str_pad($matches[3], 2, '0', STR_PAD_LEFT),
                    str_pad($matches[4], 2, '0', STR_PAD_LEFT),
                    str_pad($matches[5], 2, '0', STR_PAD_LEFT),
                    str_pad($matches[6], 2, '0', STR_PAD_LEFT),
                ];
            }
        }
    }

    /**
     *  Parse a MAC address from a well-formed MAC string and in a common format.
     *  00:12:34:ab:cd:ef
     *  00:12:34:AB:CD:EF
     *  0:12:34:AB:CD:EF
     *  00-12-34-AB-CD-EF
     *  001234-ABCDEF
     *  0012.34AB.CDEF
     *  00:02:04:0B:0D:0F
     *  0:2:4:B:D:F
     */
    public static function parse(?string $mac): static
    {
        return new static($mac ?? '');
    }

    /**
     * Remove prefix from STP bridge addresses to parse MAC
     * Examples: 80 00 3C 2C 99 7A 5D 80
     *           0-1C.2C.99.7A.5D.80
     */
    public static function parseBridge(string $bridge): static
    {
        $plainMac = new static($bridge);
        if ($plainMac->isValid()) {
            return $plainMac;
        }

        return new static(substr(preg_replace('/[^0-9a-f]/', '', strtolower($bridge)), -12));
    }

    /**
     * Check if the parsed string was a valid MAC address
     */
    public function isValid(): bool
    {
        return ! empty($this->mac);
    }

    /**
     * Reformat the MAC to 12 digit hex string 000a1fa3cc14
     */
    public function hex(): string
    {
        return implode($this->mac);
    }

    /**
     * Reformat the MAC to a nice readable format 00:0a:1f:a3:cc:14
     */
    public function readable()
    {
        return implode(':', $this->mac);
    }

    /**
     * Extract the OUI and return the vendor's name
     */
    public function vendor(): string
    {
        $oui = implode(array_slice($this->mac, 0, 3));

        $results = Cache::remember($oui, 21600, function () use ($oui) {
            return DB::table('vendor_ouis')
                ->where('oui', 'like', "$oui%") // possible matches
                ->orderBy('oui', 'desc') // so we can check longer ones first if we have them
                ->pluck('vendor', 'oui');
        });

        if (count($results) == 1) {
            return Arr::first($results);
        }

        // Then we may have a shorter prefix, so let's try them one after the other
        $mac = $this->hex();
        foreach ($results as $oui => $vendor) {
            if (str_starts_with($mac, $oui)) {
                return $vendor;
            }
        }

        return '';
    }

    /**
     * Reformat hex MAC as oid MAC (dotted-decimal)
     *
     * 00:12:34:AB:CD:EF becomes 0.18.52.171.205.239
     * 0:12:34:AB:CD:EF  becomes 0.18.52.171.205.239
     * 00:02:04:0B:0D:0F becomes 0.2.4.11.13.239
     * 0:2:4:B:D:F       becomes 0.2.4.11.13.15
     */
    public function oid(): string
    {
        return implode('.', array_map('hexdec', $this->mac));
    }

    /**
     * Get an array of the MAC address bytes
     */
    public function array(): array
    {
        return $this->mac;
    }

    public function __toString(): string
    {
        return $this->readable();
    }
}
