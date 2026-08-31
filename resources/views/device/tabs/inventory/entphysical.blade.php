<div class="panel panel-default">
    <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="panel-title">{{ __('Physical Entities') }}</h3>
        <div>
            <a href="#" class="btn btn-default btn-xs" onClick="expandTree('enttree');return false;">
                <i class="fa fa-plus fa-lg icon-theme" aria-hidden="true"></i> {{ __('Expand All') }}
            </a>
            <a href="#" class="btn btn-default btn-xs" onClick="collapseTree('enttree');return false;">
                <i class="fa fa-minus fa-lg icon-theme" aria-hidden="true"></i> {{ __('Collapse All') }}
            </a>
        </div>
    </div>
    <div class="panel-body">
        <ul class="mktree" id="enttree">
            @forelse($data['tree'] as $node)
                @include('device.tabs.inventory.entphysical-node', ['node' => $node])
            @empty
                <li class="text-center text-muted" style="list-style: none; padding: 20px;">
                    <em>{{ __('No physical entities found for this device.') }}</em>
                </li>
            @endforelse
        </ul>
    </div>
</div>
