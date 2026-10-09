<x-panel :title="__('Billed Sources')">
    <x-slot name="table">
        <div class="list-group">
            @forelse($sources as $source)
                <div class="list-group-item tw:flex tw:items-center tw:justify-between tw:gap-2">
                    <span>
                        <span class="label label-default">{{ $source::billingTypeName() }}</span>
                        {!! $source->getBillingLink() !!}
                        {{ __('on') }}
                        @if($source->device)<x-device-link :device="$source->device" />@endif
                    </span>
                    @if($removable ?? false)
                        <form action="{{ route('bill.source.detach', [$bill, $source->getMorphClass(), $source->getKey()]) }}" method="post"
                              onsubmit="return confirm(@js(__('Are you sure you wish to remove this :type?', ['type' => $source::billingTypeName()])))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-minus"></i> {{ __('Remove') }}</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="list-group-item">{{ __('There are no sources assigned to this bill') }}</div>
            @endforelse
        </div>
    </x-slot>
</x-panel>
