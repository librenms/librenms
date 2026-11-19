@foreach($vps as $vp)
    <x-panel :title="'VP' . $vp->vp_id . ' ' . $vp->vp_descr">
        <x-graph-row :type="'atmvp_' . $graph" :vars="['id' => $vp->juniAtmVp_id]" columns="responsive" />
    </x-panel>
@endforeach
