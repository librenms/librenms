@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-packages.table filter-name="device.packages" :filter-fields="$data['filterFields']" :filter="$data['filter']" :count="$data['packages']->count()"
                      :last-column="__('Size')" :empty-message="__('No packages found for this device.')">
        @foreach($data['packages'] as $package)
            <tr>
                <x-packages.name :name="$package->name" />
                <td class="tw:font-mono">{{ $package->displayVersion() }}</td>
                <td>{{ $package->arch }}</td>
                <td>{{ $package->manager }}</td>
                <td class="tw:text-right">{{ \LibreNMS\Util\Number::formatSi($package->size, 2, 0, '') }}</td>
            </tr>
        @endforeach
    </x-packages.table>
</x-device.page>
@endsection
