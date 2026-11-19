<?php

namespace App\Http\Controllers;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\PeeringdbIx;
use App\Models\PeeringdbIxPeer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use LibreNMS\Util\AutonomousSystem;

class PeeringController extends Controller
{
    private const array PER_PAGE_OPTIONS = [50, 100, 250, 500];

    /**
     * Public ASNs of the user's active devices.
     */
    public function index(Request $request): View
    {
        $this->authorize('peering-db.view');

        $asns = Device::hasAccess($request->user())
            ->where('disabled', 0)
            ->where('ignore', 0)
            ->where('bgpLocalAs', '>', 0)
            ->where(fn (Builder $query) => $query->where('bgpLocalAs', '<', 64512)->orWhere('bgpLocalAs', '>', 65535)) // private and well known
            ->where('bgpLocalAs', '<', 4200000000) // private and reserved
            ->distinct()
            ->orderBy('bgpLocalAs')
            ->pluck('bgpLocalAs')
            ->mapWithKeys(fn ($asn) => [(int) $asn => AutonomousSystem::get($asn)->name()]);

        $cached = PeeringdbIx::min('timestamp');

        return view('peering.index', [
            'asns' => $asns,
            'cached' => $cached ? Carbon::createFromTimestamp($cached, config('app.timezone')) : null,
        ]);
    }

    /**
     * Internet exchanges the ASN is present at.
     */
    public function exchanges(int $asn): View
    {
        $this->authorize('peering-db.view');

        return view('peering.exchanges', [
            'asn' => $asn,
            'exchanges' => PeeringdbIx::where('asn', $asn)->orderBy('name')->get(),
        ]);
    }

    /**
     * Peers at an internet exchange and whether a BGP session to them exists.
     */
    public function peers(Request $request, int $asn, int $ix): View
    {
        $this->authorize('peering-db.view');

        $request->validate([
            'status' => ['nullable', Rule::in(['all', 'connected', 'unconnected'])],
            'search' => 'nullable|string',
            'per_page' => ['nullable', Rule::in(self::PER_PAGE_OPTIONS)],
        ]);
        $status = $request->input('status', 'all');
        $search = $request->string('search')->value();
        $perPage = $request->integer('per_page', self::PER_PAGE_OPTIONS[0]);

        $sessions = BgpPeer::hasAccess($request->user())->select('bgpPeerIdentifier');

        $peers = PeeringdbIxPeer::where('ix_id', $ix)
            ->whereNotNull('remote_ipaddr4')
            ->when($status === 'connected', fn (Builder $query) => $query->whereIn('remote_ipaddr4', $sessions))
            ->when($status === 'unconnected', fn (Builder $query) => $query->whereNotIn('remote_ipaddr4', $sessions))
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('remote_ipaddr4', 'like', "%$search%")
                ->orWhere('remote_asn', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%")))
            ->orderBy('remote_asn')
            ->orderBy('remote_ipaddr4')
            ->paginate($perPage);

        return view('peering.peers', [
            'asn' => $asn,
            'exchange' => PeeringdbIx::where('ix_id', $ix)->where('asn', $asn)->firstOrFail(),
            'peers' => $peers,
            'connected' => BgpPeer::hasAccess($request->user())
                ->whereIn('bgpPeerIdentifier', $peers->pluck('remote_ipaddr4'))
                ->pluck('bgpPeerIdentifier')
                ->flip(),
            'status' => $status,
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}
