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

namespace LibreNMS\Util;

class Bird2
{
    /** bird pads this header to a fixed width */
    public const HEADER = 'Name       Proto      Table      State  Since         Info';

    /**
     * Parse `birdc show protocols all` into one entry per BGP protocol.
     *
     * @return list<array<string, mixed>>
     */
    public static function parseProtocols(string $output): array
    {
        $protocolsData = [];

        // Remove headers
        $birdOutput = trim(explode(self::HEADER, $output, 2)[1]);
        $protocolSegments = explode("\n\n", $birdOutput);

        // Remove the first title
        unset($protocolSegments[0]);

        foreach ($protocolSegments as $protocolSegment) {
            // Deal with the title first
            $protocolSegmentParts = explode("\n", $protocolSegment, 2);
            $titleParts = preg_split("/\s+/", $protocolSegmentParts[0], 5);

            // make sure we only look at BGP protocols
            if ($titleParts[1] !== 'BGP') {
                continue;
            }

            $protocolData = [
                'name' => $titleParts[0],
                'type' => $titleParts[1],
                'table' => $titleParts[2],
                'protocol_state' => $titleParts[3],
                'since' => preg_split("/\s+/", $titleParts[4], 3)[0] . ' ' . preg_split("/\s+/", $titleParts[4], 3)[1],
            ];

            // Deal with the rest of the body
            $protocolBodys = preg_split("/^\s{2}([A-Z])/m", $protocolSegmentParts[1]);

            // Loop through all BGP protocols
            foreach ($protocolBodys as $protocolBody) {
                // Deal with the BGP block
                if (str_starts_with($protocolBody, 'GP')) {
                    foreach (explode("\n", 'B' . $protocolBody) as $protocolBodyLine) {
                        if (str_contains($protocolBodyLine, ':')) {
                            $lineParts = explode(':', $protocolBodyLine, 2);
                            $protocolData[str_replace(' ', '_', strtolower(trim($lineParts[0])))] = trim($lineParts[1]);
                        }
                    }

                    // Fix up the error string
                    if (isset($protocolData['last_error'])) {
                        // Trim the received
                        $protocolData['last_error'] = trim(str_ireplace('Received:', '', $protocolData['last_error']));
                    }
                }

                // Process the Ip channel (v4/v6)
                $IpVersion = 4;
                if (isset($protocolData['neighbor_address']) && filter_var($protocolData['neighbor_address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $IpVersion = 6;
                }

                if (str_starts_with($protocolBody, 'hannel ipv' . $IpVersion)) {
                    foreach (explode("\n", 'C' . $protocolBody) as $protocolBodyLine) {
                        if (str_contains($protocolBodyLine, ':')) {
                            $lineParts = explode(':', $protocolBodyLine, 2);
                            $protocolData[str_replace(' ', '_', strtolower(trim($lineParts[0])))] = trim($lineParts[1]);
                        }
                    }

                    // Fix up the ROUTES
                    if (isset($protocolData['routes'])) {
                        $routeParts = explode(', ', $protocolData['routes']);
                        unset($protocolData['routes']);
                        foreach ($routeParts as $routePart) {
                            $routeDetail = explode(' ', $routePart);
                            $protocolData['routes'][$routeDetail[1]] = $routeDetail[0];
                        }
                    }

                    // Set the route updates
                    unset($protocolData['route_change_stats']);
                    foreach (['import_updates', 'import_withdraws', 'export_updates', 'export_withdraws'] as $key) {
                        if (! isset($protocolData[$key])) {
                            continue;
                        }

                        $routeChange_parts = preg_split("/\s+/", trim($protocolData[$key]));

                        unset($protocolData[$key]);
                        $protocolData['route_change_stats'][$key] = [
                            'received' => $routeChange_parts[0],
                            'rejected' => $routeChange_parts[1],
                            'filtered' => $routeChange_parts[2],
                            'ignored' => $routeChange_parts[3],
                            'accepted' => $routeChange_parts[4],
                        ];
                    }
                }
            }

            $protocolsData[] = $protocolData;
        }

        return $protocolsData;
    }
}
