{{-- a forwarding table entry: where a MAC was learned and how likely it is the access port --}}
<div class="tw:flex tw:flex-wrap tw:items-center tw:gap-x-1.5 tw:gap-y-0.5">
    @include('search.partials.port', ['port' => $fdb->port])
    @if($fdb->vlan)
        <x-search.badge>{{ __('search.columns.vlan') }} {{ $fdb->vlan->vlan_vlan }}</x-search.badge>
    @endif
    @if($fdb->port?->links_count)
        <x-search.badge color="amber" title="{{ __('Neighbor discovered on this port') }}">{{ __('search.badge.uplink') }}</x-search.badge>
    @elseif((int) $fdb->mac_count === 1)
        <x-search.badge color="green">{{ __('search.badge.only_mac') }}</x-search.badge>
    @elseif($fdb->mac_count)
        <x-search.badge>{{ __('search.badge.macs_on_port', ['count' => $fdb->mac_count]) }}</x-search.badge>
    @endif
</div>
