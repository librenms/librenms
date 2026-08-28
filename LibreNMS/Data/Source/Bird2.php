<?php

/**
 * Bird2.php
 *
 * Parses `birdc show protocols all` output for the bird2 application.
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
 */

namespace LibreNMS\Data\Source;

class Bird2
{
    public const HEADER = 'Name       Proto      Table      State  Since         Info';

    /** cbgp rrd datasets, in the order the rrd expects them */
    public const PREFIX_DATASETS = [
        'AcceptedPrefixes',
        'DeniedPrefixes',
        'AdvertisedPrefixes',
        'SuppressedPrefixes',
        'WithdrawnPrefixes',
    ];

    /**
     * Parse `birdc show protocols all` into one entry per BGP protocol.
     *
     * @return list<array<string, mixed>>
     */
    public static function parseProtocols(string $output): array
    {
        $protocolsData = [];

        $parts = explode(self::HEADER, $output, 2);
        if (! isset($parts[1])) {
            return $protocolsData;
        }

        foreach (self::splitProtocols($parts[1]) as $protocolSegment) {
            $protocolSegmentParts = explode("\n", $protocolSegment, 2);
            $titleParts = preg_split("/\s+/", $protocolSegmentParts[0], 5);

            if (($titleParts[1] ?? null) !== 'BGP' || ! isset($protocolSegmentParts[1])) {
                continue;
            }

            $protocolData = [
                'name' => $titleParts[0],
                'type' => $titleParts[1],
                'table' => $titleParts[2],
                'protocol_state' => $titleParts[3],
                'since' => preg_split("/\s+/", $titleParts[4], 3)[0] . ' ' . preg_split("/\s+/", $titleParts[4], 3)[1],
            ];

            // same indent as the blocks below, so take it before splitting them
            if (preg_match('/^\s+Description:\s*(.+)$/m', $protocolSegmentParts[1], $descriptionMatch)) {
                $protocolData['description'] = trim($descriptionMatch[1]);
            }

            $protocolBodys = preg_split("/^\s{2}([A-Z])/m", $protocolSegmentParts[1]);

            foreach ($protocolBodys as $protocolBody) {
                if (str_starts_with($protocolBody, 'GP')) {
                    $protocolData = array_merge($protocolData, self::parseKeyedLines('B' . $protocolBody));

                    if (isset($protocolData['last_error'])) {
                        $protocolData['last_error'] = trim(str_ireplace('Received:', '', $protocolData['last_error']));
                    }
                }

                if (! str_starts_with($protocolBody, 'hannel ')) {
                    continue;
                }

                $channel = self::parseChannel('C' . $protocolBody);
                $protocolData['channels'][$channel['afi'] . '.' . $channel['safi']] = $channel;
            }

            // the peer's own family also populates the peer itself
            $ipVersion = isset($protocolData['neighbor_address'])
                && filter_var($protocolData['neighbor_address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 6 : 4;
            $primaryChannel = $protocolData['channels']['ipv' . $ipVersion . '.unicast'] ?? [];
            unset($primaryChannel['afi'], $primaryChannel['safi']);

            $protocolsData[] = array_merge($protocolData, $primaryChannel);
        }

        return $protocolsData;
    }

    /**
     * Channel counters mapped onto the bgpPeers_cbgp columns.
     * Key order must match PREFIX_DATASETS, rrd updates are positional.
     *
     * @param  array<string, mixed>  $channel
     * @return array<string, int>
     */
    public static function channelPrefixCounters(array $channel): array
    {
        $counters = [
            'AcceptedPrefixes' => $channel['routes']['imported'] ?? 0,
            'DeniedPrefixes' => $channel['route_change_stats']['import_updates']['rejected'] ?? 0,
            'AdvertisedPrefixes' => $channel['routes']['exported'] ?? 0,
            'SuppressedPrefixes' => $channel['route_change_stats']['import_updates']['filtered'] ?? 0,
            'WithdrawnPrefixes' => $channel['route_change_stats']['import_withdraws']['accepted'] ?? 0,
        ];

        return array_map(fn ($value) => (int) Number::cast($value), $counters);
    }

    /**
     * Split the protocol table into one block per protocol.
     * Protocols start at column 0 and their detail is indented, blank lines are not reliable.
     *
     * @return list<string>
     */
    private static function splitProtocols(string $body): array
    {
        $segments = [];
        $current = null;

        foreach (explode("\n", $body) as $line) {
            $line = rtrim($line, "\r");

            if (trim($line) === '') {
                continue;
            }

            if (ctype_space($line[0])) {
                $current .= "\n" . $line; // continuation of the current protocol
                continue;
            }

            if ($current !== null) {
                $segments[] = $current;
            }
            $current = $line;
        }

        if ($current !== null) {
            $segments[] = $current;
        }

        return $segments;
    }

    /**
     * "Field: value" lines into snake_cased keys.
     *
     * @return array<string, string>
     */
    private static function parseKeyedLines(string $block): array
    {
        $data = [];

        foreach (explode("\n", $block) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $data[str_replace(' ', '_', strtolower(trim($key)))] = trim($value);
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private static function parseChannel(string $block): array
    {
        // "Channel ipv6" or "Channel ipv4 multicast"
        $channelParts = preg_split("/\s+/", trim(explode("\n", $block, 2)[0]));

        $channel = array_merge([
            'afi' => $channelParts[1] ?? '',
            'safi' => $channelParts[2] ?? 'unicast',
        ], self::parseKeyedLines($block));

        if (isset($channel['routes'])) {
            $routeParts = explode(', ', $channel['routes']);
            unset($channel['routes']);
            foreach ($routeParts as $routePart) {
                $routeDetail = explode(' ', $routePart);
                $channel['routes'][$routeDetail[1]] = $routeDetail[0];
            }
        }

        unset($channel['route_change_stats']);
        foreach (['import_updates', 'import_withdraws', 'export_updates', 'export_withdraws'] as $key) {
            if (! isset($channel[$key])) {
                continue;
            }

            $routeChangeParts = preg_split("/\s+/", trim($channel[$key]));

            unset($channel[$key]);
            $channel['route_change_stats'][$key] = [
                'received' => $routeChangeParts[0],
                'rejected' => $routeChangeParts[1],
                'filtered' => $routeChangeParts[2],
                'ignored' => $routeChangeParts[3],
                'accepted' => $routeChangeParts[4],
            ];
        }

        return $channel;
    }
}
