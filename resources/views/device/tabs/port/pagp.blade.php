<x-panel>
    <x-graph-row type="pagp_bits" :port="$port" columns="responsive" />
    @foreach($members as $member)
        <div><i class="fa fa-anchor fa-lg icon-theme" aria-hidden="true"></i> <strong><x-port-link :port="$member" /> (PAgP)</strong></div>
    @endforeach
</x-panel>
