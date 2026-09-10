@props(['groups'])

@if($groups->isNotEmpty())
    <x-device.overview.panel :title="__('Device Group Membership')" icon="fa fa-th" :href="url('device-groups')">
        <div class="tw:flex tw:flex-wrap tw:gap-2 tw:p-3">
            @foreach($groups as $group)
                <a href="{{ route('devices', ['filter' => ['groups.id' => ['eq' => $group->id]]]) }}">{{ $group->name }}</a>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
