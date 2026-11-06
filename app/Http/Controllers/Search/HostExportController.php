<?php

/**
 * HostExportController.php
 *
 * Download the search results as CSV, with the facts the list shows for each host
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

namespace App\Http\Controllers\Search;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Search\Data\HostKey;
use App\Http\Controllers\Search\Data\HostRecord;
use App\Http\Controllers\Search\Data\HostRow;
use App\Http\Requests\HostSearchRequest;
use App\Models\Port;
use App\Models\UserPref;
use Illuminate\Support\Enumerable;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HostExportController extends Controller
{
    private const int CHUNK_SIZE = 500;

    public function __invoke(HostSearchRequest $request): StreamedResponse
    {
        $this->authorize('viewAny', Port::class);

        $validated = $request->validated();
        $user = $request->user();

        $query = $request->listQuery();
        $rows = $query->build($request->sort(), $request->direction());
        $lookup = new HostLookup($user);
        $macFormat = (string) (UserPref::getPref($user, 'network_search_mac_format') ?? 'colon');

        return response()->streamDownload(function () use ($rows, $query, $lookup, $macFormat, $validated): void {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, $this->headers(), ',', '"', '\\');

            $write = function (Enumerable $page) use ($output, $query, $lookup, $macFormat): void {
                $hosts = $lookup->find($page->map(fn (object $row): HostKey => $query->hostKey((array) $row))->values()->all());
                foreach ($hosts as $host) {
                    foreach ($this->rows($host, $macFormat) as $row) {
                        fputcsv($output, array_map($this->neutralizeFormula(...), $row), ',', '"', '\\');
                    }
                }
            };

            if (($validated['export'] ?? 'all') === 'page') {
                $write($rows->forPage((int) ($validated['page'] ?? 1), (int) ($validated['perPage'] ?? 25))->get());
            } else {
                $rows->cursor()->chunk(self::CHUNK_SIZE)->each($write);
            }

            fclose($output);
        }, 'network-search-' . date('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return list<string>
     */
    private function headers(): array
    {
        return array_map(fn (string $key): string => __("search.export.$key"), [
            'ip', 'mac', 'vendor', 'interface', 'switch', 'port', 'port_description', 'vlan', 'macs_on_port', 'seen_via', 'last_seen',
        ]);
    }

    /**
     * @return list<list<scalar|null>>
     */
    private function rows(HostRecord $host, string $macFormat): array
    {
        $ips = implode(' ', array_map(fn (IP $ip): string => $ip->compressed(), $host->ips));

        return array_map(fn (HostRow $row): array => [
            $ips,
            $row->mac ? $this->formatMac($row->mac->hex, $macFormat) : '',
            $row->mac && LibrenmsConfig::get('mac_oui.enabled') ? Mac::parse($row->mac->hex)->vendor() : '',
            $this->portText($host->interface) ?: $host->device?->display,
            $row->location?->port?->device?->display,
            $row->location?->port?->getLabel(),
            $row->location?->portDescription(),
            $row->location?->vlan?->vlan_vlan,
            $row->location?->macCount,
            $row->location ? '' : $this->portText($host->viaPort()),
            $row->seen?->toDateTimeString(),
        ], $host->rows());
    }

    /**
     * Port descriptions and device names come from the devices, a spreadsheet would run one that looks like a formula
     */
    private function neutralizeFormula(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }

    private function portText(?Port $port): string
    {
        return $port ? trim($port->device?->display . ' ' . $port->getLabel()) : '';
    }

    private function formatMac(string $hex, string $format): string
    {
        return match ($format) {
            'dash' => implode('-', str_split($hex, 2)),
            'dot' => implode('.', str_split($hex, 4)),
            'bare' => $hex,
            default => implode(':', str_split($hex, 2)),
        };
    }
}
