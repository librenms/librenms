<div class="tw:flex tw:items-center tw:justify-between tw:pb-4 tw:-mt-4 tw:gap-4">
    <div class="tw:flex-1">
        <x-filter name="device.port-security" :fields="$data['filterFields']" :initial="$data['filter']" :reload="true"/>
    </div>
    <x-table-export
        :export-route="route('port-security.export')"
        :params="['device_id' => $device->device_id]"
        :filter="$data['filter']"
        :page="$data['portSecurity']->currentPage()"
        :per-page="$data['perPage']"
        class="tw:shrink-0"
    />
</div>

@include('port-security.includes.table', [
    'portSecurity' => $data['portSecurity'],
    'showDevice' => false,
    'perPage' => $data['perPage'],
    'paginationOptions' => ['16', '32', '128', 'all'],
])
