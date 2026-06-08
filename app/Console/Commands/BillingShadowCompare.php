<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BillingShadowCompare extends Command
{
    protected $signature = 'billing:shadow-compare
        {--bill_id= : Compare one specific bill ID}
        {--since= : Start timestamp, format: YYYY-MM-DD HH:MM:SS}
        {--until= : End timestamp, format: YYYY-MM-DD HH:MM:SS. Defaults to now}
        {--limit=10 : Maximum number of bills to compare when --bill_id is not supplied}
        {--min-port-count=1 : Only compare bills with at least this many shadow ports}
    ';

    protected $description = 'Compare shadow billing port data against existing bill_data';

    public function handle(): int
    {
        $since = $this->option('since');
        $until = $this->option('until') ?: date('Y-m-d H:i:s');
        $billId = $this->option('bill_id') !== null ? (int) $this->option('bill_id') : null;
        $limit = max(1, (int) $this->option('limit'));
        $minPortCount = max(1, (int) $this->option('min-port-count'));

        if (empty($since)) {
            $this->error('Missing required option: --since');
            $this->line("Example: ./lnms billing:shadow-compare --bill_id=538 --since='2026-06-08 13:25:00' --until='2026-06-08 14:05:00'");

            return 1;
        }

        $startTs = strtotime($since);
        $endTs = strtotime($until);

        if ($startTs === false || $endTs === false || $endTs <= $startTs) {
            $this->error('Invalid time range.');

            return 1;
        }

        $billIds = $this->getBillIds($billId, $limit, $minPortCount);

        if ($billIds->isEmpty()) {
            $this->warn('No bills found for comparison.');

            return 0;
        }

        $rows = [];

        foreach ($billIds as $id) {
            $result = $this->compareBill((int) $id, $startTs, $endTs);

            $status = 'OK';

            if ($result['shadow_ports'] < $result['member_ports']) {
                $status = 'INCOMPLETE';
            } elseif ($result['bill_total'] < 100000000) {
                $status = 'LOW_VOLUME';
            }

            $rows[] = [
                'Bill ID' => $result['bill_id'],
                'Ports' => $result['member_ports'],
                'Shadow Ports' => $result['shadow_ports'],
                'Coverage' => $result['coverage_percent'] . '%',
                'Intervals' => $result['shadow_intervals'],
                'Bill Samples' => $result['bill_samples'],
                'Shadow Total' => $result['shadow_total'],
                'Bill Total' => $result['bill_total'],
                'Diff' => $result['diff_total'],
                'Diff %' => $result['diff_percent'] === null ? '-' : $result['diff_percent'] . '%',
                'Status' => $status,
            ];
        }

        $this->line('Billing shadow comparison');
        $this->line("Range: {$since} -> {$until}");
        $this->newLine();

        $this->table(
            [
                'Bill ID',
                'Ports',
                'Shadow Ports',
                'Coverage',
                'Intervals',
                'Bill Samples',
                'Shadow Total',
                'Bill Total',
                'Diff',
                'Diff %',
                'Status',
            ],
            $rows
        );

        return 0;
    }

    private function getBillIds(?int $billId, int $limit, int $minPortCount)
    {
        if ($billId !== null && $billId > 0) {
            return collect([$billId]);
        }

        return DB::table('bill_port_data')
            ->select('bill_id')
            ->selectRaw('COUNT(DISTINCT port_id) as port_count')
            ->groupBy('bill_id')
            ->havingRaw('COUNT(DISTINCT port_id) >= ?', [$minPortCount])
            ->orderByDesc('port_count')
            ->limit($limit)
            ->pluck('bill_id');
    }

    private function compareBill(int $billId, int $startTs, int $endTs): array
    {
        $memberPorts = DB::table('bill_ports')
            ->where('bill_id', $billId)
            ->pluck('port_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $shadowRows = DB::table('bill_port_data')
            ->where('bill_id', $billId)
            ->whereIn('port_id', $memberPorts)
            ->where('timestamp', '>=', date('Y-m-d H:i:s', $startTs - 900))
            ->where('timestamp', '<=', date('Y-m-d H:i:s', $endTs + 900))
            ->orderBy('port_id')
            ->orderBy('timestamp')
            ->get()
            ->groupBy('port_id');

        $shadow = [
            'ports_seen' => [],
            'intervals_used' => 0,
            'in_delta' => 0,
            'out_delta' => 0,
            'delta' => 0,
        ];

        foreach ($shadowRows as $portId => $rows) {
            $previous = null;

            foreach ($rows as $row) {
                if ($previous === null) {
                    $previous = $row;
                    continue;
                }

                $fromTs = strtotime($previous->timestamp);
                $toTs = strtotime($row->timestamp);

                $period = $toTs - $fromTs;
                $inDelta = (int) $row->in_counter - (int) $previous->in_counter;
                $outDelta = (int) $row->out_counter - (int) $previous->out_counter;

                if ($period < 1 || $inDelta < 0 || $outDelta < 0) {
                    $previous = $row;
                    continue;
                }

                $overlapStart = max($startTs, $fromTs);
                $overlapEnd = min($endTs, $toTs);
                $overlap = $overlapEnd - $overlapStart;

                if ($overlap <= 0) {
                    $previous = $row;
                    continue;
                }

                $ratio = $overlap / $period;

                $allocatedIn = (int) round($inDelta * $ratio);
                $allocatedOut = (int) round($outDelta * $ratio);

                $shadow['ports_seen'][(int) $portId] = true;
                $shadow['intervals_used']++;
                $shadow['in_delta'] += $allocatedIn;
                $shadow['out_delta'] += $allocatedOut;
                $shadow['delta'] += ($allocatedIn + $allocatedOut);

                $previous = $row;
            }
        }

        $billRows = DB::table('bill_data')
            ->where('bill_id', $billId)
            ->where('timestamp', '>=', date('Y-m-d H:i:s', $startTs - 900))
            ->where('timestamp', '<=', date('Y-m-d H:i:s', $endTs + 900))
            ->orderBy('timestamp')
            ->get();

        $bill = [
            'samples' => 0,
            'in_delta' => 0,
            'out_delta' => 0,
            'delta' => 0,
        ];

        foreach ($billRows as $row) {
            $rowEnd = strtotime($row->timestamp);
            $rowStart = $rowEnd - (int) $row->period;

            $overlapStart = max($startTs, $rowStart);
            $overlapEnd = min($endTs, $rowEnd);
            $overlap = $overlapEnd - $overlapStart;

            if ($overlap <= 0 || (int) $row->period < 1) {
                continue;
            }

            $ratio = $overlap / (int) $row->period;

            $bill['samples']++;
            $bill['in_delta'] += (int) round((int) $row->in_delta * $ratio);
            $bill['out_delta'] += (int) round((int) $row->out_delta * $ratio);
            $bill['delta'] += (int) round((int) $row->delta * $ratio);
        }

        $memberPortCount = count($memberPorts);
        $shadowPortCount = count($shadow['ports_seen']);
        $diffTotal = $shadow['delta'] - $bill['delta'];

        return [
            'bill_id' => $billId,
            'member_ports' => $memberPortCount,
            'shadow_ports' => $shadowPortCount,
            'coverage_percent' => $memberPortCount > 0
                ? round(($shadowPortCount / $memberPortCount) * 100, 2)
                : 0,
            'shadow_intervals' => $shadow['intervals_used'],
            'bill_samples' => $bill['samples'],
            'shadow_total' => $shadow['delta'],
            'bill_total' => $bill['delta'],
            'diff_total' => $diffTotal,
            'diff_percent' => $bill['delta'] > 0
                ? round(($diffTotal / $bill['delta']) * 100, 4)
                : null,
        ];
    }
}
