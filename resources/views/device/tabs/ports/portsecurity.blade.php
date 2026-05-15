<div class="tw:pb-4 tw:-mt-4">
    <x-filter name="device.port-security" :fields="$data['filterFields']" :initial="$data['filter']" :reload="true"/>
</div>

@include('port-security.includes.table', [
    'portSecurity' => $data['portSecurity'],
    'showDevice' => false,
    'perPage' => $data['perPage'],
    'perPageParam' => 'perPage',
    'paginationOptions' => ['16', '32', '128', 'all'],
])
