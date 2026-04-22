<?php

/**
 * Snr.php
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 hrtrd
 * @author     hrtrd <neoll4ik@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Mempool;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Snr extends OS implements MempoolsDiscovery
{
    /**
     * Discover memory pool.
     *
     * SNR switches expose memory usage on the scalar branch
     * .1.3.6.1.4.1.40418.7.100.1.11:
     *   .6.0  -> total memory
     *   .7.0  -> used memory
     *   .11.0 -> memory usage percent
     *
     * Units of .6/.7 differ between models:
     *   - S2990X series reports megabytes
     *   - all other models (S298x, S299[56]G, S300X, S400X, S5210X)
     *     report bytes
     *
     * We auto-detect by magnitude: any value under 1 MiB is assumed
     * to be megabytes (no realistic device has less than 1 MiB of RAM),
     * anything at or above is treated as bytes.
     */
    public function discoverMempools(): Collection
    {
        $data = SnmpQuery::numeric()->get([
            '.1.3.6.1.4.1.40418.7.100.1.11.6.0',
            '.1.3.6.1.4.1.40418.7.100.1.11.7.0',
            '.1.3.6.1.4.1.40418.7.100.1.11.11.0',
        ])->values();

        $total_raw = (int) ($data['.1.3.6.1.4.1.40418.7.100.1.11.6.0'] ?? 0);
        $used_raw = (int) ($data['.1.3.6.1.4.1.40418.7.100.1.11.7.0'] ?? 0);
        $percent = $data['.1.3.6.1.4.1.40418.7.100.1.11.11.0'] ?? null;

        if ($total_raw <= 0) {
            return new Collection();
        }

        // Auto-detect unit: values under 1 MiB are megabytes, otherwise bytes.
        if ($total_raw < 1048576) {
            $total_bytes = $total_raw * 1024 * 1024;
            $used_bytes = $used_raw * 1024 * 1024;
        } else {
            $total_bytes = $total_raw;
            $used_bytes = $used_raw;
        }

        $mempool = new Mempool([
            'mempool_index' => 0,
            'mempool_type' => 'snr',
            'mempool_class' => 'system',
            'mempool_precision' => 1,
            'mempool_descr' => 'Memory',
            'mempool_perc_warn' => 90,
            'mempool_total' => $total_bytes,
            'mempool_used' => $used_bytes,
            'mempool_free' => $total_bytes - $used_bytes,
            'mempool_perc' => is_numeric($percent) ? (int) $percent : null,
        ]);

        $mempool->fillUsage($mempool->mempool_used, $mempool->mempool_total, $mempool->mempool_free, $mempool->mempool_perc);

        return new Collection([$mempool]);
    }
}
