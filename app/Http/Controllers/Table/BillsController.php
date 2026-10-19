<?php

namespace App\Http\Controllers\Table;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Billing;
use LibreNMS\Util\Color;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;

/**
 * @extends TableController<Bill>
 */
class BillsController extends TableController
{
    /** @var array<string, string> */
    protected array $default_sort = ['bill_name' => 'asc'];
    private bool $previous = false;

    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'period' => 'nullable|in:prev',
            'bill_type' => 'nullable|in:cdr,quota',
            'state' => 'nullable|in:under,over',
        ];
    }

    /**
     * @return string[]
     */
    protected function searchFields(Request $request): array
    {
        return ['bills.bill_name'];
    }

    /**
     * @return array<string, string>
     */
    protected function sortFields(Request $request): array
    {
        $table = $request->input('period') === 'prev' ? 'bill_history' : 'bills';

        return [
            'bill_name' => 'bills.bill_name',
            'bill_type' => "$table.bill_type",
            'bill_allowed' => 'bill_allowed',
            'total_data_in' => 'total_data_in',
            'total_data_out' => 'total_data_out',
            'total_data' => 'total_data',
            'rate_95th' => "$table.rate_95th",
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Bill::class);

        $this->previous = $request->input('period') === 'prev';

        $query = Bill::hasAccess($request->user());

        if ($this->previous) {
            $latestHistory = DB::table('bill_history')
                ->select('bill_id')
                ->selectRaw('MAX(bill_hist_id) AS bill_hist_id')
                ->where('bill_dateto', '<', now())
                ->where('bill_dateto', '>', now()->subDays(40))
                ->groupBy('bill_id');

            $query->joinSub($latestHistory, 'latest_history', 'bills.bill_id', '=', 'latest_history.bill_id')
                ->join('bill_history', 'bill_history.bill_hist_id', '=', 'latest_history.bill_hist_id')
                ->select([
                    'bills.bill_id', 'bills.bill_name', 'bills.bill_notes', 'bills.bill_day',
                    'bill_history.bill_type', 'bill_history.bill_allowed', 'bill_history.bill_percent', 'bill_history.bill_overuse',
                    'bill_history.rate_95th', 'bill_history.rate_95th_in', 'bill_history.rate_95th_out',
                    'bill_history.bill_datefrom', 'bill_history.bill_dateto',
                    'bill_history.traf_total as total_data', 'bill_history.traf_in as total_data_in', 'bill_history.traf_out as total_data_out',
                ]);
            $table = 'bill_history';
            $allowed = 'bill_history.bill_allowed';
            $used = "CASE WHEN LOWER(bill_history.bill_type) = 'cdr' THEN bill_history.rate_95th ELSE bill_history.traf_total END";
        } else {
            $allowed = "CASE WHEN LOWER(bills.bill_type) = 'cdr' THEN bills.bill_cdr ELSE bills.bill_quota END";
            $used = "CASE WHEN LOWER(bills.bill_type) = 'cdr' THEN bills.rate_95th ELSE bills.total_data END";
            $query->select('bills.*')->selectRaw("$allowed AS bill_allowed");
            $table = 'bills';
        }

        if ($request->filled('bill_type')) {
            $query->whereRaw("LOWER($table.bill_type) = ?", [$request->input('bill_type')]);
        }

        match ($request->input('state')) {
            'under' => $query->whereRaw("$used <= $allowed"),
            'over' => $query->whereRaw("$used > $allowed"),
            default => null,
        };

        return $query;
    }

    /**
     * @param  Bill  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $bill = $model;
        $allowed = (float) $bill->getAttribute('bill_allowed');

        if ($this->previous) {
            $from = Carbon::parse($bill->getAttribute('bill_datefrom'));
            $to = Carbon::parse($bill->getAttribute('bill_dateto'));
            $percent = (float) $bill->getAttribute('bill_percent');
            $overuse = (float) $bill->getAttribute('bill_overuse');
            $predicted = '-';
        } else {
            ['from' => $from, 'to' => $to] = $bill->billingPeriod();
            $percent = Number::calculatePercent($bill->used(), $allowed);
            $overuse = $bill->used() - $allowed;
            $predicted = $bill->formatUsage(Billing::getPredictedUsage($bill->bill_day, $bill->used()));
        }

        $colors = Color::percentage($percent);
        $total = Billing::formatBytes($bill->total_data);
        $rate95th = Number::formatSi($bill->rate_95th, 2, 0, 'bps');

        $actions = '';
        if (! $this->previous && Gate::allows('update', $bill)) {
            $actions = '<a href="' . route('bill.edit', $bill->bill_id) . '"><i class="fa fa-pencil fa-lg icon-theme" aria-hidden="true"></i> ' . __('Edit') . '</a>';
        }

        return [
            'bill_name' => '<a href="' . route('bill.show', $bill->bill_id) . '" class="tw:font-bold">' . e($bill->bill_name) . '</a><br />'
                . $from->format('Y-m-d') . ' ' . __('to') . ' ' . $to->format('Y-m-d'),
            'notes' => e($bill->bill_notes),
            'bill_type' => $bill->isCdr() ? 'CDR' : 'Quota',
            'bill_allowed' => $bill->formatUsage($allowed),
            'total_data_in' => $bill->isCdr() ? $bill->formatUsage($bill->rate_95th_in) : $bill->formatUsage($bill->total_data_in),
            'total_data_out' => $bill->isCdr() ? $bill->formatUsage($bill->rate_95th_out) : $bill->formatUsage($bill->total_data_out),
            'total_data' => $bill->isCdr() ? $total : "<b>$total</b>",
            'rate_95th' => $bill->isCdr() ? "<b>$rate95th</b>" : $rate95th,
            'overusage' => $overuse > 0 ? '<span style="color: #' . $colors['left'] . '; font-weight: bold;">' . $bill->formatUsage($overuse) . '</span>' : '-',
            'predicted' => $predicted,
            'graph' => Html::percentageBar(250, 10, $percent, null, $percent . '%', null, null, ['left' => $colors['left'], 'right' => $colors['right']]),
            'actions' => $actions,
        ];
    }
}
