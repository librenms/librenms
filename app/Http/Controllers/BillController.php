<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Models\Bill;
use App\Models\BillHistory;
use App\Models\Port;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LibreNMS\Billing;
use LibreNMS\Util\Number;

class BillController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Bill::class);

        $port = null;
        if ($request->filled('port') && $request->user()->can('create', Bill::class)) {
            $port = Port::hasAccess($request->user())->with('device')->find($request->integer('port'));
        }

        $newBill = new Bill([
            'bill_name' => $port?->port_descr_descr,
            'bill_ref' => $port?->port_descr_circuit,
            'bill_notes' => $port?->port_descr_speed,
            'bill_type' => 'cdr',
            'bill_day' => 1,
            'dir_95th' => LibrenmsConfig::get('billing.95th_default_agg') ? 'agg' : 'in',
        ]);

        return view('bill.index', [
            'filters' => [
                'bill_type' => $request->query('bill_type', ''),
                'state' => $request->query('state', ''),
            ],
            'newBill' => $newBill,
            'form' => ['quota' => '', 'quota_type' => 'GB', 'cdr' => '', 'cdr_type' => 'Mbps'],
            'port' => $port,
        ]);
    }

    public function store(StoreBillRequest $request): RedirectResponse
    {
        $port = null;
        if ($request->filled('port_id')) {
            $port = Port::findOrFail($request->integer('port_id'));
            $this->authorize('view', $port);
        }

        $attributes = $request->billAttributes();

        $bill = Bill::create(array_merge($attributes, [
            'dir_95th' => $attributes['dir_95th'] ?? 'in',
            'rate_95th_in' => 0,
            'rate_95th_out' => 0,
            'rate_95th' => 0,
            'total_data' => 0,
            'total_data_in' => 0,
            'total_data_out' => 0,
            'rate_average' => 0,
            'rate_average_in' => 0,
            'rate_average_out' => 0,
            'bill_last_calc' => now(),
            'bill_autoadded' => 0,
        ]));

        if ($port !== null) {
            $bill->ports()->attach($port->port_id);
        }

        toast()->success(__('Bill Created'));

        return redirect()->route('bill.edit', $bill);
    }

    public function show(Bill $bill): View
    {
        return $this->graphsPage($bill, 'quick');
    }

    public function accurate(Bill $bill): View
    {
        return $this->graphsPage($bill, 'accurate');
    }

    public function transfer(Bill $bill): View
    {
        $this->authorize('view', $bill);

        $period = $bill->billingPeriod();
        $daysElapsed = (int) $period['from']->diffInDays(now()) + 1;
        $totalDays = max(1, (int) round($period['from']->diffInDays($period['to'])));
        $total = (float) $bill->total_data;
        $quota = (float) $bill->bill_quota;
        $allowed = $bill->isCdr() ? '-' : Billing::formatBytes($quota);

        $makeRow = fn (string $label, float $used, float $percent) => [
            'label' => $label,
            'used' => Billing::formatBytes($used),
            'allowed' => $allowed,
            'average' => Billing::formatBytes($used / $daysElapsed),
            'estimated' => Billing::formatBytes($used / $daysElapsed * $totalDays),
            'percent' => $percent,
        ];

        $rows = [
            $makeRow(__('Transferred'), $total, Number::calculatePercent($total, $bill->isCdr() ? $total / $daysElapsed * $totalDays : $quota)),
            $makeRow(__('Inbound'), (float) $bill->total_data_in, Number::calculatePercent($bill->total_data_in, $total)),
            $makeRow(__('Outbound'), (float) $bill->total_data_out, Number::calculatePercent($bill->total_data_out, $total)),
        ];

        if (! $bill->isCdr() && $total > $quota) {
            $rows[] = $makeRow(__('Overusage'), $total - $quota, Number::calculatePercent($total, $quota) - 100);
        }

        $now = now();

        return $this->page($bill, 'transfer', [
            'period' => $period,
            'rows' => $rows,
            'graphs' => [
                __('Billing Period View') => ['from' => $period['from']->timestamp, 'to' => $period['to']->timestamp, 'imgtype' => 'day'],
                __('Rolling 24 Hour View') => ['from' => $now->copy()->subDay()->timestamp, 'to' => $now->timestamp, 'imgtype' => 'hour'],
                __('Rolling Monthly View') => ['from' => $now->copy()->subMonth()->timestamp, 'to' => $now->timestamp, 'imgtype' => 'day'],
            ],
        ]);
    }

    public function history(Request $request, Bill $bill): View
    {
        $this->authorize('view', $bill);

        $detail = $request->query('detail');

        $history = $bill->history()
            ->orderByDesc('bill_datefrom')
            ->limit(24)
            ->get()
            ->map(function (BillHistory $entry) use ($detail) {
                $isCdr = strtolower((string) $entry->bill_type) === 'cdr';
                $format = fn ($value) => $isCdr ? Number::formatSi($value, 2, 0, 'bps') : Billing::formatBytes($value);

                return [
                    'id' => $entry->bill_hist_id,
                    'from' => Carbon::parse($entry->bill_datefrom),
                    'to' => Carbon::parse($entry->bill_dateto),
                    'type' => $isCdr ? 'CDR' : 'Quota',
                    'is_cdr' => $isCdr,
                    'allowed' => $format($entry->bill_allowed),
                    'in' => $isCdr ? $format($entry->rate_95th_in) : $format($entry->traf_in),
                    'out' => $isCdr ? $format($entry->rate_95th_out) : $format($entry->traf_out),
                    'peak_in' => Billing::formatBytes($entry->bill_peak_in),
                    'peak_out' => Billing::formatBytes($entry->bill_peak_out),
                    'total' => Billing::formatBytes($entry->traf_total),
                    'rate_95th' => Number::formatSi($entry->rate_95th, 2, 0, 'bps'),
                    'overuse' => $entry->bill_overuse > 0 ? $format($entry->bill_overuse) : null,
                    'percent' => (float) $entry->bill_percent,
                    'show_detail' => $detail === 'all' || $detail == $entry->bill_hist_id,
                ];
            });

        return $this->page($bill, 'history', [
            'history' => $history,
        ]);
    }

    public function edit(Bill $bill): View
    {
        $this->authorize('update', $bill);

        [$quota, $quotaType] = $this->toDisplayUnits((float) $bill->bill_quota, UpdateBillRequest::QUOTA_UNITS, 'GB');
        [$cdr, $cdrType] = $this->toDisplayUnits((float) $bill->bill_cdr, UpdateBillRequest::CDR_UNITS, 'Mbps');

        $bill->load(['ports' => fn ($query) => $query->with('device')->orderBy('ports.device_id')]);

        return $this->page($bill, 'edit', [
            'form' => [
                'quota' => $bill->isCdr() ? '' : $quota,
                'quota_type' => $quotaType,
                'cdr' => $bill->isCdr() ? $cdr : '',
                'cdr_type' => $cdrType,
            ],
        ]);
    }

    public function update(UpdateBillRequest $request, Bill $bill): RedirectResponse|JsonResponse
    {
        $attributes = $request->billAttributes();
        $attributes['dir_95th'] ??= $bill->dir_95th ?? 'in';

        $bill->update($attributes);

        toast()->success(__('Bill Properties Updated'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Bill Properties Updated')]);
        }

        return redirect()->route('bill.edit', $bill);
    }

    public function destroy(Request $request, Bill $bill): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $bill);

        $bill->delete();

        toast()->success(__('Bill Deleted'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Bill Deleted')]);
        }

        return redirect()->route('bills.index');
    }

    public function reset(Request $request, Bill $bill): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $bill);

        $request->validate(['confirm' => ['accepted']]);

        $bill->history()->delete();
        $bill->data()->delete();

        toast()->success(__('Bill Reset'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Bill Reset')]);
        }

        return redirect()->route('bill.show', $bill);
    }

    public function attachPort(Request $request, Bill $bill): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $bill);

        $validated = $request->validate([
            'port_id' => ['required', 'integer', 'exists:ports,port_id'],
        ]);

        $port = Port::findOrFail($validated['port_id']);
        $this->authorize('view', $port);

        $bill->ports()->syncWithoutDetaching([$port->port_id]);

        toast()->success(__('Port added to bill'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Port added to bill')]);
        }

        return redirect()->route('bill.edit', $bill);
    }

    public function detachPort(Request $request, Bill $bill, Port $port): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $bill);

        $bill->ports()->detach($port->port_id);

        toast()->success(__('Port removed from bill'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Port removed from bill')]);
        }

        return redirect()->route('bill.edit', $bill);
    }

    /**
     * Redirect old bill/bill_id=X/view=Y urls to the new pages
     */
    public function legacyRedirect(Bill $bill, string $vars = ''): RedirectResponse
    {
        preg_match('#view=([a-z]+)#', $vars, $matches);

        $route = match ($matches[1] ?? null) {
            'accurate' => 'bill.accurate',
            'transfer' => 'bill.transfer',
            'history' => 'bill.history',
            'edit', 'delete', 'reset' => 'bill.edit',
            default => 'bill.show',
        };

        return redirect()->route($route, $bill, 301);
    }

    private function graphsPage(Bill $bill, string $view): View
    {
        $this->authorize('view', $bill);

        $period = $bill->billingPeriod();
        $now = now();
        $usageVar = $bill->isCdr() ? ['95th' => 'yes'] : ['ave' => 'yes'];

        $graph = $view == 'accurate'
            ? ['type' => 'bill_historicbits', 'width' => 1190, 'height' => 250, 'vars' => ['id' => $bill->bill_id, ...$usageVar]]
            : ['type' => 'bill_bits', 'width' => 1000, 'height' => 200, 'vars' => ['id' => $bill->bill_id, 'total' => 1, 'dir' => $bill->dir_95th, ...$usageVar]];

        return $this->page($bill, $view, [
            'period' => $period,
            'percent' => Number::calculatePercent($bill->used(), $bill->allowed()),
            'predicted' => Billing::getPredictedUsage($bill->bill_day, $bill->used()),
            'graph' => $graph,
            'graphs' => [
                __('Billing View') => ['from' => $period['from']->timestamp, 'to' => $period['to']->timestamp],
                __('24 Hour View') => ['from' => $now->copy()->subDay()->timestamp, 'to' => $now->timestamp],
                __('Monthly View') => ['from' => $now->copy()->subMonth()->timestamp, 'to' => $now->timestamp],
            ],
        ], 'show');
    }

    private function page(Bill $bill, string $view, array $data, ?string $template = null): View
    {
        $bill->loadMissing(['ports.device']);

        return view('bill.' . ($template ?? $view), array_merge([
            'bill' => $bill,
            'view' => $view,
            'dateFormat' => LibrenmsConfig::get('dateformat.date', 'D, M j, Y'),
        ], $data));
    }

    /**
     * Convert a raw bits/bytes value to the largest unit where the value is at least 1
     *
     * @param  array<string, int>  $units  unit => exponent of the billing base
     * @return array{0: float|int|string, 1: string}
     */
    private function toDisplayUnits(float $value, array $units, string $default): array
    {
        $base = (int) LibrenmsConfig::get('billing.base', 1000);

        arsort($units);
        foreach ($units as $unit => $exponent) {
            $converted = $value / $base ** $exponent;
            if ($converted >= 1) {
                return [Number::cast($converted), $unit];
            }
        }

        return ['', $default];
    }
}
