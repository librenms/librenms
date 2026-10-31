<?php

/**
 * SearchTerm.php
 *
 * Classify a free form search string as IP, network, MAC and/or vendor text
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

namespace LibreNMS\Util;

use Illuminate\Support\Facades\DB;

class SearchTerm
{
    /** @var array{short: list<string>, long: list<string>}|null */
    private ?array $vendorOuis = null;

    /**
     * @param  string  $value  the trimmed search string
     * @param  IP|null  $ip  a complete address, or network when $isNetwork
     * @param  bool  $isNetwork  a prefix length was given (10.0.0.0/8)
     * @param  string|null  $ipv4Fragment  partial IPv4 address (10.1.)
     * @param  string|null  $ipv6Fragment  partial IPv6 address (2001:db8:)
     * @param  string|null  $macHex  lowercase hex digits of a full or partial MAC
     */
    public function __construct(
        public readonly string $value,
        public readonly ?IP $ip = null,
        public readonly bool $isNetwork = false,
        public readonly ?string $ipv4Fragment = null,
        public readonly ?string $ipv6Fragment = null,
        public readonly ?string $macHex = null,
    ) {
    }

    public static function parse(string $search): self
    {
        $value = trim($search);
        $isNetwork = str_contains($value, '/');
        $ip = IP::parse($value, true);
        $hex = strtolower((string) preg_replace('/^0x|[:\-. ]/i', '', $value));
        // Mac::parse() accepts single digit groups without separators, so 74:29:72 would be a full MAC
        $mac = Mac::parse($value);
        $isFullMac = $mac->isValid() && (strlen($hex) == 12 || count(preg_split('/[:\-.]/', $value)) == 6);

        $ipv4Fragment = null;
        $ipv6Fragment = null;
        if ($ip === null) {
            if (preg_match('/^(?=.*\.)\.?\d{1,3}(\.\d{0,3}){0,3}$/', $value)) {
                $ipv4Fragment = $value;
            } elseif (! $isFullMac && preg_match('/^[0-9a-f:]+$/i', $value) && str_contains($value, ':')) {
                $ipv6Fragment = strtolower($value);
            }
        }

        // short hex strings are usually words, only treat them as MAC when they have separators or 6+ digits
        $macHex = null;
        if ($isFullMac) {
            $macHex = $mac->hex();
        } elseif ($ip === null && $ipv4Fragment === null && ctype_xdigit($hex)
            && (strlen($hex) >= 6 || preg_match('/[:\-.]/', $value))) {
            $macHex = substr($hex, 0, 12);
        }

        return new self($value, $ip, $isNetwork && $ip !== null, $ipv4Fragment, $ipv6Fragment, $macHex);
    }

    public function like(): string
    {
        return '%' . $this->value . '%';
    }

    public function isIpv4(): bool
    {
        return $this->ipv4Fragment !== null || $this->ip instanceof IPv4;
    }

    public function isIpv6(): bool
    {
        return $this->ipv6Fragment !== null || $this->ip instanceof IPv6;
    }

    public function isIp(): bool
    {
        return $this->isIpv4() || $this->isIpv6();
    }

    public function isExactIp(): bool
    {
        return $this->ip !== null && ! $this->isNetwork;
    }

    public function isMac(): bool
    {
        return $this->macHex !== null;
    }

    public function isFullMac(): bool
    {
        return $this->macHex !== null && strlen($this->macHex) == 12;
    }

    /**
     * Text that is not an address, could be a hostname or vendor
     */
    public function isText(): bool
    {
        return ! $this->isIp() && ! $this->isMac() && preg_match('/[a-z]/i', $this->value) === 1;
    }

    /**
     * Looks like a DNS name that could be resolved (contains a dot and a letter, no spaces)
     */
    public function isHostname(): bool
    {
        return $this->isText() && str_contains($this->value, '.')
            && preg_match('/^[a-z0-9]([a-z0-9\-_.]*[a-z0-9])?\.?$/i', $this->value) === 1;
    }

    /**
     * Upper case hex of the first and last address in the network, to compare with HEX(INET6_ATON(column))
     *
     * @return array{string, string}|null
     */
    public function networkRange(): ?array
    {
        if ($this->ip === null || ! $this->isNetwork) {
            return null;
        }

        $start = '';
        $end = '';
        foreach (str_split($this->ip->packed()) as $index => $byte) {
            $bits = max(0, min(8, $this->ip->cidr - $index * 8));
            $mask = (0xFF << (8 - $bits)) & 0xFF;
            $start .= chr(ord($byte) & $mask);
            $end .= chr(ord($byte) | (~$mask & 0xFF));
        }

        return [strtoupper(bin2hex($start)), strtoupper(bin2hex($end))];
    }

    /**
     * OUI prefixes of vendors matching this text.
     * 6 digit OUIs are separate so they can be matched with LEFT(mac, 6) IN (...)
     *
     * @return array{short: list<string>, long: list<string>}
     */
    public function vendorOuis(): array
    {
        if ($this->vendorOuis === null) {
            $ouis = strlen($this->value) >= 3 && $this->isText() && ! $this->isHostname()
                ? DB::table('vendor_ouis')->where('vendor', 'like', $this->like())->limit(5000)->pluck('oui')
                : collect();

            [$short, $long] = $ouis->map(fn ($oui) => strtolower((string) $oui))
                ->partition(fn ($oui) => strlen($oui) == 6);

            $this->vendorOuis = ['short' => $short->values()->all(), 'long' => $long->take(200)->values()->all()];
        }

        return $this->vendorOuis;
    }

    public function isVendor(): bool
    {
        $ouis = $this->vendorOuis();

        return ! empty($ouis['short']) || ! empty($ouis['long']);
    }
}
