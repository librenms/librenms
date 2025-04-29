<x-panel body-class="tw:p-0!">
    <table id="ports-fdb" class="table table-condensed table-hover table-striped tw:mt-1 tw:mb-0!">
        <thead>
        <tr>
            <th width="350"><a href="{{ $request->fullUrlWithQuery(['sort' => 'port', 'order' => $data['sort'] == 'port' ? $data['next_order'] : 'asc']) }} ">{{ __('Port') }}</a></th>
            <th width="100" class="tw:hidden tw:md:table-cell">{{ __('Port Groups') }}</th>
            <th width="100">{{ __('Graphs') }}</th>
            <th width="120"><a href="{{ $request->fullUrlWithQuery(['sort' => 'traffic', 'order' => $data['sort'] == 'traffic' ? $data['next_order'] : 'desc']) }} ">{{ __('Traffic') }}</a></th>
            <th width="75"><a href="{{ $request->fullUrlWithQuery(['sort' => 'speed', 'order' => $data['sort'] == 'speed' ? $data['next_order'] : 'desc']) }} ">{{ __('Speed') }}</a></th>
            <th width="100" class="tw:hidden tw:sm:table-cell"><a href="{{ $request->fullUrlWithQuery(['sort' => 'media', 'order' => $data['sort'] == 'media' ? $data['next_order'] : 'asc']) }} ">{{ __('Media') }}</a></th>
            <th width="100"><a href="{{ $request->fullUrlWithQuery(['sort' => 'mac', 'order' => $data['sort'] == 'mac' ? $data['next_order'] : 'asc']) }} ">{{ __('MAC Address') }}</a></th>
            <th width="375" class="tw:hidden tw:md:table-cell"></th>
        </tr>
        </thead>
        @foreach($data['ports'] as $port)
            @include('device.tabs.ports.includes.port_row', ['collapsing' => true])
        @endforeach
    </table>
    <div class="tw:flex tw:flex-row-reverse tw:m-3">
        {{ $data['ports']->links('pagination::tailwind', ['perPage' => $data['perPage']]) }}
        @isset($data['perPage'])
            <x-select :options="['16', '32', '128', 'all']"
                      x-on:change="
                      const params = new URLSearchParams(window.location.search);
                      params.set('perPage', $event.target.value);
                      params.delete('page');
                      window.location.search = params.toString();
                      " x-data="{}"
                      selected="{{ $data['perPage'] }}"
                      name="perPage"
                      label="{{ __('Per Page') }}"
                      class="tw:mx-4"></x-select>
        @endisset
    </div>
</x-panel>
