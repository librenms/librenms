<x-panel>
    <x-slot:heading class="tw:flex tw:items-center tw:justify-between">
        <h3 class="panel-title">{{ __('Physical Entities') }}</h3>
        <div class="tw:flex tw:gap-1">
            <a href="#" class="btn btn-default btn-xs" onclick="expandTree('enttree'); return false;">
                <i class="fa fa-plus fa-lg icon-theme" aria-hidden="true"></i> {{ __('Expand All') }}
            </a>
            <a href="#" class="btn btn-default btn-xs" onclick="collapseTree('enttree'); return false;">
                <i class="fa fa-minus fa-lg icon-theme" aria-hidden="true"></i> {{ __('Collapse All') }}
            </a>
        </div>
    </x-slot>

    <ul class="mktree" id="enttree">
        @forelse($data['tree'] as $node)
            @include('device.tabs.inventory.entphysical-node', ['node' => $node])
        @empty
            <li class="text-center text-muted tw:list-none tw:p-5">
                <em>{{ __('No physical entities found for this device.') }}</em>
            </li>
        @endforelse
    </ul>
</x-panel>
