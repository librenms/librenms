<x-panel :title="__('Billed Ports')">
    <x-slot name="table">
        <div class="list-group">
            @forelse($bill->ports as $port)
                <div class="list-group-item tw:flex tw:items-center tw:justify-between tw:gap-2">
                    <span>
                        <x-port-link :port="$port" />
                        {{ __('on') }}
                        @if($port->device)<x-device-link :device="$port->device" />@endif
                    </span>
                    @if($removable ?? false)
                        <form action="{{ route('bill.port.detach', [$bill, $port]) }}" method="post"
                              onsubmit="return confirm(@js(__('Are you sure you wish to remove this port?')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-minus"></i> {{ __('Remove') }}</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="list-group-item">{{ __('There are no ports assigned to this bill') }}</div>
            @endforelse
        </div>
    </x-slot>
</x-panel>
