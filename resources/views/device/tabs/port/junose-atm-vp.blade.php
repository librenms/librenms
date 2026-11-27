@foreach($vps as $vp)
    <x-panel :title="'VP' . $vp->vp_id . ' ' . $vp->vp_descr">
        <x-graph-row :type="'port_atmvp_' . $graph" :vars="['id' => $vp->port_id, 'vp' => $vp->vp_id]" columns="responsive" />
    </x-panel>
@endforeach
