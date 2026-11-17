{{-- how many MACs share the port, coloured by how likely it is the host's own --}}
<span class="label {{ $location->state()->labelClass() }} tw:cursor-help" title="{{ $location->isUplink() ? __('search.host.uplink') : __('search.list.macs_on_port') }}">{{ trans_choice('search.macs_count', $location->macCount) }}{{ $location->isUplink() ? ' ↑' : '' }}</span>
