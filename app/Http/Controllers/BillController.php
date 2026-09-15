<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Http\Requests\UpdateBillRequest;
use App\Models\Bill;
use App\Models\Port;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

class BillController extends Controller
{
    use AuthorizesRequests;

    public function update(UpdateBillRequest $request, Bill $bill): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $base = (int) LibrenmsConfig::get('billing.base', 1000);
        $billQuota = 0;
        $billCdr = 0;

        if ($validated['bill_type'] === 'quota' && isset($validated['bill_quota'])) {
            $unit = $base == 1024 ? str_replace('B', 'iB', $request->input('bill_quota_type', 'MB')) : $request->input('bill_quota_type', 'MB');
            $bytes = Number::toBytes("{$validated['bill_quota']} $unit");
            $billQuota = is_nan($bytes) ? 0 : (int) $bytes;
        } elseif ($validated['bill_type'] === 'cdr' && isset($validated['bill_cdr'])) {
            $unit = $base == 1024 ? str_replace('bps', 'ibps', $request->input('bill_cdr_type', 'Mbps')) : $request->input('bill_cdr_type', 'Mbps');
            $bits = Number::toBytes("{$validated['bill_cdr']} $unit");
            $billCdr = is_nan($bits) ? 0 : (int) $bits;
        }

        $bill->update([
            'bill_name' => $validated['bill_name'],
            'bill_day' => $validated['bill_day'],
            'bill_quota' => $billQuota,
            'bill_cdr' => $billCdr,
            'bill_type' => $validated['bill_type'],
            'dir_95th' => $validated['dir_95th'] ?? $bill->dir_95th ?? 'in',
            'bill_custid' => (string) ($validated['bill_custid'] ?? ''),
            'bill_ref' => (string) ($validated['bill_ref'] ?? ''),
            'bill_notes' => (string) ($validated['bill_notes'] ?? ''),
        ]);

        toast()->success(__('Bill Properties Updated'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Bill Properties Updated')]);
        }

        return redirect()->to(Url::generate(['page' => 'bill', 'bill_id' => $bill->bill_id, 'view' => 'edit']));
    }

    public function destroy(Request $request, Bill $bill): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $bill);

        $bill->delete();

        toast()->success(__('Bill Deleted'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Bill Deleted')]);
        }

        return redirect(url('bills'));
    }

    public function reset(Request $request, Bill $bill): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $bill);

        $confirm = $request->input('confirm');

        if ($confirm === 'mysql') {
            $bill->history()->delete();
            $bill->data()->delete();
        }

        toast()->success(__('Bill Resetting'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Bill Resetting')]);
        }

        return redirect(url('bills'));
    }

    public function attachPort(Request $request, Bill $bill): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $bill);

        $validated = $request->validate([
            'port_id' => ['required', 'integer', 'exists:ports,port_id'],
        ]);

        $bill->ports()->syncWithoutDetaching([$validated['port_id']]);

        toast()->success(__('Port added to bill'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Port added to bill')]);
        }

        return redirect()->to(Url::generate(['page' => 'bill', 'bill_id' => $bill->bill_id, 'view' => 'edit']));
    }

    public function detachPort(Request $request, Bill $bill, Port $port): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $bill);

        $bill->ports()->detach($port->port_id);

        toast()->success(__('Port removed from bill'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Port removed from bill')]);
        }

        return redirect()->to(Url::generate(['page' => 'bill', 'bill_id' => $bill->bill_id, 'view' => 'edit']));
    }
}
